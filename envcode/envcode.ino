#include <WiFi.h>
#include <WebServer.h>
#include <Preferences.h>
#include <DNSServer.h>
#include <HTTPClient.h>
#include <time.h>
#include <Wire.h>
#include "RTClib.h"
#include <Adafruit_GFX.h>
#include <Adafruit_SH110X.h>
#include <DHT.h>
#include <math.h>
#include <esp_arduino_version.h>

/*
WIRING REFERENCE (ESP32):

1) DS3231 RTC
   - VCC -> 3V3
   - GND -> GND
   - SDA -> GPIO32
   - SCL -> GPIO33

2) OLED SH110X (I2C)
   - VCC -> 3V3
   - GND -> GND
   - SDA -> GPIO25
   - SCL -> GPIO26

3) DHT22
   - VCC -> 3V3
   - GND -> GND
   - DATA -> GPIO27
   - If your DHT22 module is bare sensor, add 10k pull-up from DATA to 3V3

4) MQ135
   - VCC -> 5V (or module-rated supply)
   - GND -> GND (shared with ESP32)
   - AO  -> GPIO34 (ADC input)
   - If AO can exceed 3.3V, use a voltage divider before GPIO34

5) Active Buzzer (2-pin)
   - (+) -> GPIO18
   - (-) -> GND

6) PIR Motion Sensor
   - VCC -> 5V / VIN
   - GND -> GND
   - OUT -> GPIO17
*/

// ---------------- PIN CONFIG ----------------
#define RTC_SDA   32
#define RTC_SCL   33

#define OLED_SDA  25
#define OLED_SCL  26

#define DHT_PIN   27
#define DHT_TYPE  DHT22

#define MQ135_PIN 34
#define BUZZER_PIN 18
#define PIR_PIN 17
#define DOOR_RELAY_PIN 16
#define DOOR_RELAY_ACTIVE HIGH
#define DOOR_RELAY_INACTIVE LOW
#define LIGHT_PIN 23
#define LIGHT_ON_LEVEL HIGH
#define LIGHT_OFF_LEVEL LOW
#define FAN_PIN 19
#define FAN_PWM_CHANNEL 2
#define FAN_PWM_FREQ 25000
#define FAN_PWM_RESOLUTION 8
const unsigned long FAN_SPINUP_MS = 220;

// ---------------- OLED CONFIG ----------------
#define SCREEN_WIDTH   128
#define SCREEN_HEIGHT   64
#define OLED_ADDR     0x3C

// ---------------- WIFI / PORTAL ----------------
const char* AP_SSID = "FORTIROOM-MAIN";
const char* AP_PASS = "";
const byte DNS_PORT = 53;
const unsigned long WIFI_CONNECT_TIMEOUT_MS = 20000;
const unsigned long WIFI_TCP_CONNECT_TIMEOUT_MS = 8000;
const char* PREF_NAMESPACE = "fortiroom";
const bool USE_HARDCODED_WIFI = true;
const char* HARDCODED_WIFI_SSID = "Guest@HELP";
const char* HARDCODED_WIFI_PASS = "guEST@HELP";
const char* DEFAULT_SERVER_BASE_URL = "http://10.150.215.215/Fortiroom";
const char* ENV_REGISTRY_PATH = "/esp32_env_registry.php";
const char* BOOKING_STATUS_PATH = "/env_booking_status.php";
const char* ENV_DEVICE_ID = "fortiroom-main";
const unsigned long ENV_REGISTRY_REPORT_INTERVAL_MS = 60000;
const unsigned long ENV_REGISTRY_RETRY_INTERVAL_MS = 15000;
const unsigned long BOOKING_POLL_MS = 5000;
const unsigned long CAMERA_BUTTON_POLL_MS = 700;
const unsigned long DOOR_UNLOCK_MS = 5000;
const unsigned long DOOR_RELOCKING_MS = 1200;
const unsigned long CHECKOUT_MOTION_MONITOR_MS = 15UL * 60UL * 1000UL;
const unsigned long CHECKOUT_MOTION_ARM_DELAY_MS = 2500;

// ---------------- NTP ----------------
const long GMT_OFFSET_SEC = 8 * 3600;
const int DAYLIGHT_OFFSET_SEC = 0;
const char* NTP1 = "pool.ntp.org";
const char* NTP2 = "time.google.com";
const char* NTP3 = "time.cloudflare.com";
const unsigned long NTP_RESYNC_MS = 6UL * 60UL * 60UL * 1000UL;

// ---------------- Refresh rates ----------------
const unsigned long SENSOR_REFRESH_MS = 1000;
const unsigned long DHT_REFRESH_MS = 2500;
const unsigned long OLED_REFRESH_MS   = 1000;

// ---------------- AQ Display Realism ----------------
const int BASELINE_AIRIDX = 60;
const int AIRIDX_MAX = 500;

// ---------------- AQ Alarm ----------------
const int AIR_UNHEALTHY_THRESHOLD = 101;
const int AIR_HYSTERESIS_OFF = 101;
const unsigned long BUZZ_ON_MS = 700;
const unsigned long BUZZ_OFF_MS = 200;

// ---------------- MQ Auto Calibration ----------------
const unsigned long MQ_CAL_MS = 10000;
const unsigned long MQ_CAL_SAMPLE_MS = 100;

// ---------------- I2C BUSES ----------------
TwoWire I2C_RTC = TwoWire(0);
TwoWire I2C_OLED = TwoWire(1);

RTC_DS3231 rtc;
Adafruit_SH1106G display(SCREEN_WIDTH, SCREEN_HEIGHT, &I2C_OLED, -1);
DHT dht(DHT_PIN, DHT_TYPE);

WebServer server(80);
Preferences prefs;
DNSServer dnsServer;

struct DeviceConfig {
  String ssid;
  String password;
};

DeviceConfig deviceConfig;

// ---------------- Wi-Fi state ----------------
bool portalMode = false;
bool wifiReady = false;
bool ntpSyncedThisBoot = false;
unsigned long lastNtpSyncMs = 0;
unsigned long lastEnvRegistryReportMs = 0;
bool lastEnvRegistryReportOk = false;

enum DoorPhase {
  DOOR_IDLE = 0,
  DOOR_COOLDOWN,
  DOOR_RELOCKING,
  DOOR_AQI_RELEASE
};

DoorPhase gDoorPhase = DOOR_IDLE;
unsigned long gDoorPhaseStartedMs = 0;
unsigned long gDoorUnlockCount = 0;
bool gLightOn = false;
int gFanRequestedSpeed = 0;
int gFanSpeed = 0;
char gFanMode = 'A';
unsigned long gFanSpinupUntilMs = 0;
int gFanTargetDuty = 0;
static float gTempC = NAN;
static float gHum = NAN;
static int gMqRaw = 0;
static float gMqEma = NAN;
static int gAirIdx = 0;
static const char* gAirLabel = "Good";
static bool buzzerActive = false;
static bool buzzerOutputOn = false;
static unsigned long buzzerLastToggleMs = 0;

struct BookingStatusState {
  int bookingsTodayCount = 0;
  String relevantState;
  bool verificationWindowOpen = false;
  String primaryUsername;
  String secondaryUsername;
  String displayName;
  String windowKey;
  bool cameraOnline = false;
  String cameraButtonUrl;
  String cameraTriggerCaptureUrl;
  String cameraCaptureUrl;
  String cameraBaseUrl;
  bool verificationInProgress = false;
  bool verifiedThisWindow = false;
  String verifiedBookingKey;
};

static BookingStatusState gBookingStatus;
static unsigned long lastBookingPollMs = 0;
static unsigned long lastCameraButtonPollMs = 0;
static unsigned long statusMessageUntilMs = 0;
static String statusMessageLine1;
static String statusMessageLine2;
static String statusMessageLine3;
static String gLastBookingWindowKey;
static TaskHandle_t gVerificationTaskHandle = nullptr;
static volatile bool gVerificationTaskRunning = false;
static volatile bool gVerificationResultReady = false;
static volatile bool gVerificationResultVerified = false;
static String gVerificationRequestUsername;
static String gVerificationMatchedUsername;
static String gVerificationMatchedRole;

static bool gCheckoutMonitorActive = false;
static bool gCheckoutPenaltyReported = false;
static unsigned long gCheckoutMonitorStartedMs = 0;
static String gCheckoutBookingId;
static String gCheckoutPodId;
static String gCheckoutPrimaryUserId;
static String gCheckoutSecondaryUserId;

// ---------------- Helpers ----------------
static void print2(Print &p, int v) {
  if (v < 10) p.print('0');
  p.print(v);
}

static inline float clampf(float x, float lo, float hi) {
  if (x < lo) return lo;
  if (x > hi) return hi;
  return x;
}

static float ema(float prev, float current, float alpha) {
  return prev + alpha * (current - prev);
}

static const char* aqiLabel(int idx) {
  if (idx <= 50) return "Good";
  if (idx <= 100) return "Moderate";
  if (idx <= 150) return "Unhealthy(SG)";
  if (idx <= 200) return "Unhealthy";
  if (idx <= 300) return "Very Unhealthy";
  return "Hazardous";
}

static String htmlEscape(const String &value) {
  String out = value;
  out.replace("&", "&amp;");
  out.replace("<", "&lt;");
  out.replace(">", "&gt;");
  out.replace("\"", "&quot;");
  out.replace("'", "&#39;");
  return out;
}

static String jsonSafeFloat(float v, int decimals) {
  if (isnan(v)) return String("null");
  return String(v, decimals);
}

static String jsonEscape(const String &value) {
  String out;
  out.reserve(value.length() + 8);
  for (size_t i = 0; i < value.length(); i++) {
    char c = value.charAt(i);
    switch (c) {
      case '\\': out += "\\\\"; break;
      case '"': out += "\\\""; break;
      case '\n': out += "\\n"; break;
      case '\r': out += "\\r"; break;
      case '\t': out += "\\t"; break;
      default: out += c; break;
    }
  }
  return out;
}

static int jsonExtractInt(const String &json, const char *key, int fallback) {
  String needle = "\"" + String(key) + "\"";
  int pos = json.indexOf(needle);
  if (pos < 0) return fallback;
  int colon = json.indexOf(':', pos + needle.length());
  if (colon < 0) return fallback;
  int end = colon + 1;
  while (end < (int)json.length() && (json.charAt(end) == ' ' || json.charAt(end) == '\t')) end++;
  int valueEnd = end;
  while (valueEnd < (int)json.length() && (isDigit(json.charAt(valueEnd)) || json.charAt(valueEnd) == '-')) valueEnd++;
  if (valueEnd <= end) return fallback;
  return json.substring(end, valueEnd).toInt();
}

static bool jsonExtractBool(const String &json, const char *key, bool fallback) {
  String needle = "\"" + String(key) + "\"";
  int pos = json.indexOf(needle);
  if (pos < 0) return fallback;
  int colon = json.indexOf(':', pos + needle.length());
  if (colon < 0) return fallback;
  int start = colon + 1;
  while (start < (int)json.length() && (json.charAt(start) == ' ' || json.charAt(start) == '\t')) start++;
  if (json.startsWith("true", start)) return true;
  if (json.startsWith("false", start)) return false;
  return fallback;
}

static String jsonExtractString(const String &json, const char *key, const String &fallback = "") {
  String needle = "\"" + String(key) + "\"";
  int pos = json.indexOf(needle);
  if (pos < 0) return fallback;
  int colon = json.indexOf(':', pos + needle.length());
  if (colon < 0) return fallback;
  int firstQuote = json.indexOf('"', colon + 1);
  if (firstQuote < 0) return fallback;
  String out;
  bool escaped = false;
  for (int i = firstQuote + 1; i < (int)json.length(); i++) {
    char c = json.charAt(i);
    if (escaped) {
      switch (c) {
        case 'n': out += '\n'; break;
        case 'r': out += '\r'; break;
        case 't': out += '\t'; break;
        default: out += c; break;
      }
      escaped = false;
      continue;
    }
    if (c == '\\') {
      escaped = true;
      continue;
    }
    if (c == '"') {
      return out;
    }
    out += c;
  }
  return fallback;
}

static const char* doorPhaseName() {
  switch (gDoorPhase) {
    case DOOR_COOLDOWN: return "cooldown";
    case DOOR_RELOCKING: return "relocking";
    case DOOR_AQI_RELEASE: return "aqi_release";
    case DOOR_IDLE:
    default: return "idle";
  }
}

static const char* doorStatusText() {
  return (gDoorPhase == DOOR_COOLDOWN || gDoorPhase == DOOR_AQI_RELEASE) ? "Unlocked" : "Locked";
}

static bool doorIsBusy() {
  return gDoorPhase != DOOR_IDLE;
}

static void applyDoorRelay(bool unlocked) {
  digitalWrite(DOOR_RELAY_PIN, unlocked ? DOOR_RELAY_ACTIVE : DOOR_RELAY_INACTIVE);
}

static void applyLightOutput(bool on) {
  gLightOn = on;
  digitalWrite(LIGHT_PIN, on ? LIGHT_ON_LEVEL : LIGHT_OFF_LEVEL);
}

static void initFanPwm() {
#if ESP_ARDUINO_VERSION_MAJOR >= 3
  ledcAttach(FAN_PIN, FAN_PWM_FREQ, FAN_PWM_RESOLUTION);
#else
  ledcSetup(FAN_PWM_CHANNEL, FAN_PWM_FREQ, FAN_PWM_RESOLUTION);
  ledcAttachPin(FAN_PIN, FAN_PWM_CHANNEL);
#endif
}

static void writeFanDuty(uint8_t duty) {
#if ESP_ARDUINO_VERSION_MAJOR >= 3
  ledcWrite(FAN_PIN, duty);
#else
  ledcWrite(FAN_PWM_CHANNEL, duty);
#endif
}

static uint8_t fanSpeedToDuty(int speed) {
  speed = constrain(speed, 0, 5);
  switch (speed) {
    case 0: return 0;
    case 1: return 150;
    case 2: return 180;
    case 3: return 205;
    case 4: return 230;
    default: return 255;
  }
}

static const char* fanReasonText() {
  if (buzzerActive) return "aqi_alarm";
  if (gFanMode == 'M') return "manual";
  if (gFanRequestedSpeed <= 0) return "idle";
  if (isnan(gTempC)) return "auto_base";
  if (gTempC >= 29.5f) return "auto_hot_max";
  if (gTempC >= 28.0f) return "auto_hot";
  if (gTempC <= 21.5f) return "auto_cool_low";
  if (gTempC <= 24.0f) return "auto_cool";
  return "auto_base";
}

static int resolveFanSpeed() {
  if (buzzerActive) {
    return 5;
  }

  int baseSpeed = constrain(gFanRequestedSpeed, 0, 5);

  if (gFanMode == 'M') {
    return baseSpeed;
  }

  if (baseSpeed <= 0) {
    return 0;
  }

  if (isnan(gTempC)) {
    return baseSpeed;
  }

  if (gTempC >= 29.5f) return 5;
  if (gTempC >= 28.0f) return 4;
  if (gTempC <= 21.5f) return 1;
  if (gTempC <= 24.0f) return 2;
  return 3;
}

static void applyFanOutput(int speed) {
  gFanSpeed = constrain(speed, 0, 5);
  gFanTargetDuty = fanSpeedToDuty(gFanSpeed);

  if (gFanSpeed <= 0) {
    gFanSpinupUntilMs = 0;
    writeFanDuty(0);
    return;
  }

  // Give the fan a short full-power kick when starting or ramping up
  // so it responds more reliably at lower manual levels.
  gFanSpinupUntilMs = millis() + FAN_SPINUP_MS;
  writeFanDuty(255);
}

static void updateFanOutput() {
  if (gFanSpeed <= 0) {
    writeFanDuty(0);
    return;
  }

  if (gFanSpinupUntilMs != 0 && millis() < gFanSpinupUntilMs) {
    writeFanDuty(255);
    return;
  }

  gFanSpinupUntilMs = 0;
  writeFanDuty((uint8_t) gFanTargetDuty);
}

static void syncFanControl() {
  int resolvedSpeed = resolveFanSpeed();
  if (resolvedSpeed == gFanSpeed) {
    return;
  }
  applyFanOutput(resolvedSpeed);
}

static void startDoorUnlock() {
  gDoorPhase = DOOR_COOLDOWN;
  gDoorPhaseStartedMs = millis();
  gDoorUnlockCount++;
  applyDoorRelay(true);
  Serial.println("Door relay triggered: UNLOCKED");
}

static void syncDoorControl() {
  if (buzzerActive) {
    if (gDoorPhase != DOOR_AQI_RELEASE) {
      gDoorPhase = DOOR_AQI_RELEASE;
      gDoorPhaseStartedMs = millis();
      applyDoorRelay(true);
      Serial.println("Door relay: AQI EMERGENCY RELEASE");
    }
    return;
  }

  if (gDoorPhase == DOOR_AQI_RELEASE) {
    gDoorPhase = DOOR_IDLE;
    gDoorPhaseStartedMs = millis();
    applyDoorRelay(false);
    Serial.println("Door relay: AQI RELEASE CLEARED, LOCKED");
  }
}

static void updateDoorRelay() {
  if (gDoorPhase == DOOR_AQI_RELEASE) {
    applyDoorRelay(true);
    return;
  }

  unsigned long now = millis();

  if (gDoorPhase == DOOR_COOLDOWN && now - gDoorPhaseStartedMs >= DOOR_UNLOCK_MS) {
    gDoorPhase = DOOR_RELOCKING;
    gDoorPhaseStartedMs = now;
    applyDoorRelay(false);
    Serial.println("Door relay: RELOCKING");
  } else if (gDoorPhase == DOOR_RELOCKING && now - gDoorPhaseStartedMs >= DOOR_RELOCKING_MS) {
    gDoorPhase = DOOR_IDLE;
    gDoorPhaseStartedMs = now;
    applyDoorRelay(false);
    Serial.println("Door relay: LOCKED");
  }
}

// ---------------- Live sensor values ----------------
static unsigned long lastSensorRefreshMs = 0;
static unsigned long lastOledRefreshMs = 0;
static unsigned long lastDhtRefreshMs = 0;

static int gMqClean = 500;
static int gMqDirty = 2500;

// ---------------- Display helpers ----------------
static void setContrast(uint8_t c) {
  display.oled_command(SH110X_SETCONTRAST);
  display.oled_command(c);
}

static void fade(uint8_t fromC, uint8_t toC, int stepDelay = 4) {
  if (fromC < toC) {
    for (int c = fromC; c <= toC; c += 3) {
      setContrast((uint8_t)c);
      delay(stepDelay);
    }
  } else {
    for (int c = fromC; c >= toC; c -= 3) {
      setContrast((uint8_t)c);
      delay(stepDelay);
    }
  }
  setContrast(toC);
}

static void showTitle() {
  display.clearDisplay();
  display.setTextColor(SH110X_WHITE);
  display.setTextSize(2);

  int16_t x1, y1;
  uint16_t w, h;
  display.getTextBounds("FORTIROOM", 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 22);
  display.println("FORTIROOM");
  display.display();
}

static void showTagline() {
  display.clearDisplay();
  display.setTextColor(SH110X_WHITE);
  display.setTextSize(1);

  int16_t x1, y1;
  uint16_t w, h;

  display.getTextBounds("Smart", 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 16);
  display.println("Smart");

  display.getTextBounds("Secure", 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 28);
  display.println("Secure");

  display.getTextBounds("Seamless", 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 40);
  display.println("Seamless");

  display.display();
}

static void drawLoadingFrame(const char* message, const char* phase, uint8_t frame, float progress01, bool showPercent) {
  display.clearDisplay();
  display.setTextColor(SH110X_WHITE);
  display.setTextSize(1);

  int16_t x1, y1;
  uint16_t w, h;
  display.getTextBounds(message, 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 10);
  display.println(message);

  if (phase && strlen(phase) > 0) {
    display.getTextBounds(phase, 0, 0, &x1, &y1, &w, &h);
    display.setCursor((128 - w) / 2, 22);
    display.println(phase);
  }

  const int cx = 64, cy = 38, r = 7;
  for (int i = 0; i < 8; i++) {
    float a = (frame + i) * 0.785398f;
    int x = cx + (int)(cos(a) * r);
    int y = cy + (int)(sin(a) * r);
    if (i == 0 || i == 1) display.fillCircle(x, y, 1, SH110X_WHITE);
    else display.drawPixel(x, y, SH110X_WHITE);
  }

  progress01 = clampf(progress01, 0.0f, 1.0f);
  const int barX = 14, barY = 52, barW = 100, barH = 8;
  display.drawRoundRect(barX, barY, barW, barH, 3, SH110X_WHITE);
  int fillW = (int)((barW - 2) * progress01 + 0.5f);
  if (fillW > 0) display.fillRoundRect(barX + 1, barY + 1, fillW, barH - 2, 2, SH110X_WHITE);

  if (showPercent) {
    int pct = (int)(progress01 * 100.0f + 0.5f);
    char pctBuf[8];
    snprintf(pctBuf, sizeof(pctBuf), "%d%%", pct);
    display.getTextBounds(pctBuf, 0, 0, &x1, &y1, &w, &h);
    display.setCursor((128 - w) / 2, 42);
    display.println(pctBuf);
  }
  display.display();
}

static void drawEnvScreen(const DateTime &now, bool ntpOk) {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SH110X_WHITE);
  char timeBuf[12];
  snprintf(timeBuf, sizeof(timeBuf), "%02d:%02d:%02d", now.hour(), now.minute(), now.second());

  int16_t x1, y1;
  uint16_t w, h;
  display.getTextBounds(timeBuf, 0, 0, &x1, &y1, &w, &h);
  display.setCursor((SCREEN_WIDTH - w) / 2, 2);
  display.println(timeBuf);

  display.setCursor(104, 2);
  display.print(ntpOk ? "NTP" : "RTC");

  bool showingStatus = statusMessageUntilMs != 0 && millis() < statusMessageUntilMs;
  if (showingStatus) {
    display.getTextBounds(statusMessageLine1, 0, 0, &x1, &y1, &w, &h);
    display.setCursor((SCREEN_WIDTH - w) / 2, 19);
    display.println(statusMessageLine1);

    if (statusMessageLine2.length() > 0) {
      display.getTextBounds(statusMessageLine2, 0, 0, &x1, &y1, &w, &h);
      display.setCursor((SCREEN_WIDTH - w) / 2, 33);
      display.println(statusMessageLine2);
    }

    if (statusMessageLine3.length() > 0) {
      display.getTextBounds(statusMessageLine3, 0, 0, &x1, &y1, &w, &h);
      display.setCursor((SCREEN_WIDTH - w) / 2, 47);
      display.println(statusMessageLine3);
    }
  } else if ((gBookingStatus.relevantState == "upcoming" || gBookingStatus.relevantState == "active") &&
             gBookingStatus.displayName.length() > 0) {
    String line1 = "Booking for";
    String line2 = gBookingStatus.displayName;
    if (line2.length() > 20) {
      line2 = line2.substring(0, 20);
    }
    String line3 = (gBookingStatus.relevantState == "upcoming") ? "Upcoming" : "Active";

    display.getTextBounds(line1, 0, 0, &x1, &y1, &w, &h);
    display.setCursor((SCREEN_WIDTH - w) / 2, 18);
    display.println(line1);

    display.getTextBounds(line2, 0, 0, &x1, &y1, &w, &h);
    display.setCursor((SCREEN_WIDTH - w) / 2, 32);
    display.println(line2);

    display.getTextBounds(line3, 0, 0, &x1, &y1, &w, &h);
    display.setCursor((SCREEN_WIDTH - w) / 2, 46);
    display.println(line3);
  } else {
    String label = "No. of Bookings Today:";
    char countBuf[8];
    snprintf(countBuf, sizeof(countBuf), "%d", gBookingStatus.bookingsTodayCount);

    display.getTextBounds(label, 0, 0, &x1, &y1, &w, &h);
    display.setCursor((SCREEN_WIDTH - w) / 2, 20);
    display.println(label);

    display.setTextSize(2);
    display.getTextBounds(countBuf, 0, 0, &x1, &y1, &w, &h);
    display.setCursor((SCREEN_WIDTH - w) / 2, 36);
    display.println(countBuf);
    display.setTextSize(1);
  }
  display.display();
}

static void drawPortalScreen() {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SH110X_WHITE);
  display.setCursor(0, 0);
  display.println("WiFi Setup Mode");
  display.setCursor(0, 14);
  display.println("AP: FORTIROOM-MAIN");
  display.setCursor(0, 28);
  display.print("IP: ");
  display.println(WiFi.softAPIP());
  display.setCursor(0, 42);
  display.println("Open browser");
  display.setCursor(0, 54);
  display.println("to configure WiFi");
  display.display();
}

// ---------------- MQ mapping ----------------
static int mq135ToIndexDynamic(int raw) {
  int clean = gMqClean;
  int dirty = gMqDirty;

  if (dirty <= clean + 50) dirty = clean + 50;
  if (raw <= clean) return BASELINE_AIRIDX;

  float norm = (raw - clean) / (float)(dirty - clean);
  norm = clampf(norm, 0.0f, 1.0f);

  int idx = BASELINE_AIRIDX + (int)(norm * (AIRIDX_MAX - BASELINE_AIRIDX) + 0.5f);
  if (idx > AIRIDX_MAX) idx = AIRIDX_MAX;
  return idx;
}

// ---------------- Wi-Fi config storage ----------------
static void loadConfig() {
  prefs.begin(PREF_NAMESPACE, true);
  deviceConfig.ssid = prefs.getString("ssid", "");
  deviceConfig.password = prefs.getString("pass", "");
  prefs.end();
}

static void saveConfig(const DeviceConfig &config) {
  prefs.begin(PREF_NAMESPACE, false);
  prefs.putString("ssid", config.ssid);
  prefs.putString("pass", config.password);
  prefs.end();
}

static void clearSavedWiFiConfig() {
  prefs.begin(PREF_NAMESPACE, false);
  prefs.remove("ssid");
  prefs.remove("pass");
  prefs.end();
}

// ---------------- Portal HTML ----------------
static String buildCountdownHtml(const String &title, const String &message, int seconds) {
  String html;
  html += "<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>";
  html += "<title>" + htmlEscape(title) + "</title>";
  html += "<style>body{font-family:Arial,sans-serif;background:#0f1720;color:#f5f7fa;margin:0;padding:24px}section{max-width:520px;margin:48px auto;background:#18212d;border-radius:16px;padding:24px}h1{margin-top:0}.count{font-size:48px;font-weight:bold;margin:12px 0}</style>";
  html += "</head><body><section><h1>" + htmlEscape(title) + "</h1>";
  html += "<p>" + htmlEscape(message) + "</p>";
  html += "<div class='count' id='count'>" + String(seconds) + "</div>";
  html += "<script>let s=" + String(seconds) + ";const el=document.getElementById('count');const t=setInterval(()=>{s--;if(s<=0){el.textContent='0';clearInterval(t);}else{el.textContent=String(s);}},1000);</script>";
  html += "</section></body></html>";
  return html;
}

static String buildStatusHtml() {
  String html;
  html += "<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>";
  html += "<title>FORTIROOM MAIN</title>";
  html += "<style>";
  html += "body{font-family:Arial,sans-serif;background:#10161d;color:#eef2f7;margin:0;padding:24px}";
  html += "section{background:#18212b;border-radius:16px;padding:18px;margin-bottom:16px}";
  html += "input,button{width:100%;box-sizing:border-box;padding:12px;margin:6px 0 12px;border-radius:10px;border:1px solid #425468}";
  html += "input{background:#0f1720;color:#fff}button{background:#17a34a;color:#fff;font-weight:bold;border:0;cursor:pointer}";
  html += ".danger{background:#c0392b}.mono{font-family:Consolas,monospace}";
  html += "</style></head><body>";
  html += "<section><h1>FORTIROOM MAIN</h1>";
  html += "<p><strong>Mode:</strong> ";
  html += portalMode ? "AP Configuration Portal" : "Wi-Fi Connected";
  html += "<br><strong>IP:</strong> <span class='mono'>" + htmlEscape(portalMode ? WiFi.softAPIP().toString() : WiFi.localIP().toString()) + "</span>";
  if (!portalMode) {
    html += "<br><strong>SSID:</strong> " + htmlEscape(WiFi.SSID());
    html += "<br><strong>RSSI:</strong> " + String(WiFi.RSSI());
    html += "<br><strong>Door:</strong> " + String(doorStatusText());
  }
  html += "</p></section>";

  if (portalMode) {
    html += "<section><h2>Connect to Wi-Fi</h2>";
    html += "<form method='post' action='/save-config'>";
    html += "<label>SSID</label><input name='ssid' value='" + htmlEscape(deviceConfig.ssid) + "' required>";
    html += "<label>Password</label><input name='password' type='password'>";
    html += "<button type='submit'>Save and Connect</button>";
    html += "</form></section>";
  } else {
    html += "<section><h2>Device Status</h2>";
    html += "<p>RTC, DHT22, MQ135 and OLED are running.</p>";
    html += "<p><a href='/api/time'>/api/time</a><br><a href='/api/sensors'>/api/sensors</a><br><a href='/api/door'>/api/door</a><br><a href='/unlock'>/unlock</a></p>";
    html += "</section>";
  }

  html += "<section><h2>Actions</h2>";
  html += "<form method='post' action='/reset-wifi'><button class='danger' type='submit'>Reset Saved Wi-Fi</button></form>";
  html += "<p><a href='/health'>/health</a></p>";
  html += "</section></body></html>";
  return html;
}

static String normalizedServerBaseUrl(const String &raw) {
  String url = raw;
  url.trim();
  while (url.endsWith("/")) {
    url.remove(url.length() - 1);
  }
  return url;
}

static String getServerBaseUrl() {
  return normalizedServerBaseUrl(String(DEFAULT_SERVER_BASE_URL));
}

// ---------------- Web handlers ----------------
static void handleRoot() {
  server.sendHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
  server.sendHeader("Pragma", "no-cache");
  server.sendHeader("Expires", "0");
  server.send(200, "text/html", buildStatusHtml());
}

static void handleCaptivePortalProbe() {
  if (portalMode) {
    handleRoot();
    return;
  }
  handleRoot();
}

static void handleHealth() {
  String json = "{";
  json += "\"status\":\"ok\",";
  json += "\"mode\":\"" + String(portalMode ? "ap" : "sta") + "\",";
  json += "\"ip\":\"" + String(portalMode ? WiFi.softAPIP().toString() : WiFi.localIP().toString()) + "\",";
  json += "\"ssid\":\"" + htmlEscape(WiFi.SSID()) + "\",";
  json += "\"wifi_ready\":" + String(wifiReady ? "true" : "false") + ",";
  json += "\"ntp_synced\":" + String(ntpSyncedThisBoot ? "true" : "false") + ",";
  json += "\"door_phase\":\"" + String(doorPhaseName()) + "\",";
  json += "\"door_status\":\"" + String(doorStatusText()) + "\",";
  json += "\"door_busy\":" + String(doorIsBusy() ? "true" : "false");
  json += "}";
  server.send(200, "application/json", json);
}

static void handleApiDoor() {
  unsigned long elapsedMs = millis() - gDoorPhaseStartedMs;
  unsigned long remainingMs = 0;
  if (gDoorPhase == DOOR_COOLDOWN) {
    remainingMs = elapsedMs >= DOOR_UNLOCK_MS ? 0 : (DOOR_UNLOCK_MS - elapsedMs);
  } else if (gDoorPhase == DOOR_RELOCKING) {
    remainingMs = elapsedMs >= DOOR_RELOCKING_MS ? 0 : (DOOR_RELOCKING_MS - elapsedMs);
  }

  String json = "{";
  json += "\"status\":\"" + String(doorStatusText()) + "\",";
  json += "\"phase\":\"" + String(doorPhaseName()) + "\",";
  json += "\"busy\":" + String(doorIsBusy() ? "true" : "false") + ",";
  json += "\"aqi_release_active\":" + String(gDoorPhase == DOOR_AQI_RELEASE ? "true" : "false") + ",";
  json += "\"relay_active_low\":true,";
  json += "\"relay_pin\":" + String(DOOR_RELAY_PIN) + ",";
  json += "\"remaining_ms\":" + String(remainingMs) + ",";
  json += "\"unlock_count\":" + String(gDoorUnlockCount);
  json += "}";
  server.send(200, "application/json", json);
}

static void handleApiLight() {
  String json = "{";
  json += "\"ok\":true,";
  json += "\"light_on\":" + String(gLightOn ? "true" : "false") + ",";
  json += "\"pin\":" + String(LIGHT_PIN);
  json += "}";
  server.send(200, "application/json", json);
}

static void handleFan() {
  if (server.method() == HTTP_GET) {
    String json = "{";
    json += "\"ok\":true,";
    json += "\"fan_speed\":" + String(gFanSpeed) + ",";
    json += "\"fan_requested_speed\":" + String(gFanRequestedSpeed) + ",";
    json += "\"fan_mode\":\"" + String(gFanMode) + "\",";
    json += "\"fan_reason\":\"" + String(fanReasonText()) + "\",";
    json += "\"pin\":" + String(FAN_PIN) + ",";
    json += "\"fan_target_duty\":" + String(gFanTargetDuty);
    json += "}";
    server.send(200, "application/json", json);
    return;
  }

  if (portalMode || WiFi.status() != WL_CONNECTED) {
    server.send(503, "application/json", "{\"ok\":false,\"error\":\"Device is not in normal Wi-Fi mode\"}");
    return;
  }

  String body = server.arg("plain");
  int requestedSpeed = gFanRequestedSpeed;
  char requestedMode = gFanMode;

  int speedPos = body.indexOf("\"fan_speed\"");
  if (speedPos >= 0) {
    int colon = body.indexOf(':', speedPos);
    if (colon >= 0) {
      requestedSpeed = body.substring(colon + 1).toInt();
    }
  }

  int modePos = body.indexOf("\"fan_mode\"");
  if (modePos >= 0) {
    int colon = body.indexOf(':', modePos);
    int quote1 = body.indexOf('"', colon + 1);
    int quote2 = body.indexOf('"', quote1 + 1);
    if (quote1 >= 0 && quote2 > quote1) {
      String modeValue = body.substring(quote1 + 1, quote2);
      if (modeValue.length() > 0) {
        requestedMode = modeValue.charAt(0);
      }
    }
  }

  requestedSpeed = constrain(requestedSpeed, 0, 5);
  if (requestedMode != 'A' && requestedMode != 'M') {
    requestedMode = 'A';
  }

  gFanMode = requestedMode;
  gFanRequestedSpeed = requestedSpeed;
  syncFanControl();

  String json = "{";
  json += "\"ok\":true,";
  json += "\"fan_speed\":" + String(gFanSpeed) + ",";
  json += "\"fan_requested_speed\":" + String(gFanRequestedSpeed) + ",";
  json += "\"fan_mode\":\"" + String(gFanMode) + "\",";
  json += "\"fan_reason\":\"" + String(fanReasonText()) + "\",";
  json += "\"pin\":" + String(FAN_PIN) + ",";
  json += "\"fan_target_duty\":" + String(gFanTargetDuty);
  json += "}";
  server.send(200, "application/json", json);
}

static void handleDoorUnlock() {
  if (portalMode || WiFi.status() != WL_CONNECTED) {
    server.send(503, "application/json", "{\"ok\":false,\"error\":\"Device is not in normal Wi-Fi mode\"}");
    return;
  }

  if (gDoorPhase == DOOR_AQI_RELEASE) {
    String json = "{";
    json += "\"ok\":false,";
    json += "\"error\":\"Door is in AQI emergency release mode\",";
    json += "\"phase\":\"" + String(doorPhaseName()) + "\",";
    json += "\"status\":\"" + String(doorStatusText()) + "\"";
    json += "}";
    server.send(409, "application/json", json);
    return;
  }

  if (doorIsBusy()) {
    String json = "{";
    json += "\"ok\":false,";
    json += "\"error\":\"Door relay is busy\",";
    json += "\"phase\":\"" + String(doorPhaseName()) + "\",";
    json += "\"status\":\"" + String(doorStatusText()) + "\"";
    json += "}";
    server.send(409, "application/json", json);
    return;
  }

  startDoorUnlock();

  String json = "{";
  json += "\"ok\":true,";
  json += "\"phase\":\"" + String(doorPhaseName()) + "\",";
  json += "\"status\":\"" + String(doorStatusText()) + "\",";
  json += "\"unlock_ms\":" + String(DOOR_UNLOCK_MS) + ",";
  json += "\"relocking_ms\":" + String(DOOR_RELOCKING_MS) + ",";
  json += "\"unlock_count\":" + String(gDoorUnlockCount);
  json += "}";
  server.send(200, "application/json", json);
}

static void handleLightToggle() {
  if (portalMode || WiFi.status() != WL_CONNECTED) {
    server.send(503, "application/json", "{\"ok\":false,\"error\":\"Device is not in normal Wi-Fi mode\"}");
    return;
  }

  String body = server.arg("plain");
  int lightPos = body.indexOf("\"light_on\"");
  if (lightPos >= 0) {
    int colon = body.indexOf(':', lightPos);
    if (colon >= 0) {
      String value = body.substring(colon + 1);
      value.trim();
      bool requestedOn = value.startsWith("true") || value.startsWith("1");
      applyLightOutput(requestedOn);
    } else {
      applyLightOutput(!gLightOn);
    }
  } else {
    applyLightOutput(!gLightOn);
  }

  Serial.print("Light toggled: ");
  Serial.println(gLightOn ? "ON" : "OFF");

  String json = "{";
  json += "\"ok\":true,";
  json += "\"light_on\":" + String(gLightOn ? "true" : "false") + ",";
  json += "\"pin\":" + String(LIGHT_PIN);
  json += "}";
  server.send(200, "application/json", json);
}

static void handleApiTime() {
  DateTime now = rtc.now();
  char dt[32];
  snprintf(dt, sizeof(dt), "%02d/%02d/%04d %02d:%02d:%02d",
           now.day(), now.month(), now.year(),
           now.hour(), now.minute(), now.second());

  String json = "{";
  json += "\"datetime\":\"" + String(dt) + "\",";
  json += "\"time_source\":\"" + String(ntpSyncedThisBoot ? "NTP" : "RTC") + "\",";
  json += "\"ip\":\"" + String(portalMode ? WiFi.softAPIP().toString() : WiFi.localIP().toString()) + "\"";
  json += "}";
  server.send(200, "application/json", json);
}

static void handleApiSensors() {
  String json = "{";
  json += "\"temp_c\":" + jsonSafeFloat(gTempC, 2) + ",";
  json += "\"hum\":" + jsonSafeFloat(gHum, 2) + ",";
  json += "\"mq_raw\":" + String(gMqRaw) + ",";
  json += "\"mq_ema\":" + String((int)(isnan(gMqEma) ? 0 : (gMqEma + 0.5f))) + ",";
  json += "\"mq_clean\":" + String(gMqClean) + ",";
  json += "\"air_index\":" + String(gAirIdx) + ",";
  json += "\"air_label\":\"" + String(gAirLabel) + "\",";
  json += "\"alarm_threshold\":" + String(AIR_UNHEALTHY_THRESHOLD) + ",";
  json += "\"buzzer_active\":" + String(buzzerActive ? "true" : "false") + ",";
  json += "\"fan_speed\":" + String(gFanSpeed) + ",";
  json += "\"fan_requested_speed\":" + String(gFanRequestedSpeed) + ",";
  json += "\"fan_mode\":\"" + String(gFanMode) + "\",";
  json += "\"fan_reason\":\"" + String(fanReasonText()) + "\",";
  json += "\"fan_target_duty\":" + String(gFanTargetDuty) + ",";
  json += "\"motion_detected\":" + String(digitalRead(PIR_PIN) == HIGH ? "true" : "false") + ",";
  json += "\"checkout_monitor_active\":" + String(gCheckoutMonitorActive ? "true" : "false");
  json += "}";
  server.send(200, "application/json", json);
}

static void handleCheckoutComplete() {
  if (portalMode || WiFi.status() != WL_CONNECTED) {
    server.send(503, "application/json", "{\"ok\":false,\"error\":\"Device is not in normal Wi-Fi mode\"}");
    return;
  }

  String body = server.arg("plain");
  gCheckoutBookingId = jsonExtractString(body, "booking_id", "");
  gCheckoutPodId = jsonExtractString(body, "pod_id", "");
  gCheckoutPrimaryUserId = jsonExtractString(body, "primary_user_id", "");
  gCheckoutSecondaryUserId = jsonExtractString(body, "secondary_user_id", "");
  gCheckoutMonitorStartedMs = millis();
  gCheckoutMonitorActive = true;
  gCheckoutPenaltyReported = false;

  applyLightOutput(false);
  gFanMode = 'A';
  gFanRequestedSpeed = 0;
  syncFanControl();
  showTransientStatus("Checkout", "Complete", "Thank you", 6000);

  String json = "{";
  json += "\"ok\":true,";
  json += "\"checkout_monitor_active\":true,";
  json += "\"pir_pin\":" + String(PIR_PIN) + ",";
  json += "\"monitor_ms\":" + String(CHECKOUT_MOTION_MONITOR_MS);
  json += "}";
  server.send(200, "application/json", json);
}

static void handleSaveConfig() {
  DeviceConfig updated;
  updated.ssid = server.arg("ssid");
  updated.password = server.arg("password");

  saveConfig(updated);
  server.send(200, "text/html", buildCountdownHtml("Configuration saved", "Device will reboot in:", 2));
  delay(2000);
  ESP.restart();
}

static void handleResetWiFi() {
  clearSavedWiFiConfig();
  server.send(200, "text/html", buildCountdownHtml("Saved Wi-Fi cleared", "Device will reboot into setup mode in:", 2));
  delay(2000);
  ESP.restart();
}

static void registerRoutes() {
  server.on("/", HTTP_GET, handleRoot);
  server.on("/generate_204", HTTP_GET, handleCaptivePortalProbe);
  server.on("/gen_204", HTTP_GET, handleCaptivePortalProbe);
  server.on("/hotspot-detect.html", HTTP_GET, handleCaptivePortalProbe);
  server.on("/library/test/success.html", HTTP_GET, handleCaptivePortalProbe);
  server.on("/success.txt", HTTP_GET, handleCaptivePortalProbe);
  server.on("/canonical.html", HTTP_GET, handleCaptivePortalProbe);
  server.on("/connecttest.txt", HTTP_GET, handleCaptivePortalProbe);
  server.on("/ncsi.txt", HTTP_GET, handleCaptivePortalProbe);
  server.on("/fwlink", HTTP_GET, handleCaptivePortalProbe);
  server.on("/redirect", HTTP_GET, handleCaptivePortalProbe);
  server.on("/health", HTTP_GET, handleHealth);
  server.on("/api/time", HTTP_GET, handleApiTime);
  server.on("/api/sensors", HTTP_GET, handleApiSensors);
  server.on("/api/door", HTTP_GET, handleApiDoor);
  server.on("/api/light", HTTP_GET, handleApiLight);
  server.on("/fan", HTTP_GET, handleFan);
  server.on("/fan", HTTP_POST, handleFan);
  server.on("/unlock", HTTP_GET, handleDoorUnlock);
  server.on("/unlock", HTTP_POST, handleDoorUnlock);
  server.on("/checkout", HTTP_POST, handleCheckoutComplete);
  server.on("/light-toggle", HTTP_GET, handleLightToggle);
  server.on("/light-toggle", HTTP_POST, handleLightToggle);
  server.on("/save-config", HTTP_POST, handleSaveConfig);
  server.on("/reset-wifi", HTTP_POST, handleResetWiFi);

  server.onNotFound([]() {
    if (portalMode) {
      String host = WiFi.softAPIP().toString();
      server.sendHeader("Location", "http://" + host + "/", true);
      server.send(302, "text/plain", "Redirecting...");
      return;
    }
    server.send(404, "application/json", "{\"error\":\"Not found\"}");
  });
}

// ---------------- Wi-Fi / NTP ----------------
static bool connectToSavedWiFi() {
  if (USE_HARDCODED_WIFI) {
    deviceConfig.ssid = HARDCODED_WIFI_SSID;
    deviceConfig.password = HARDCODED_WIFI_PASS;
  }

  if (deviceConfig.ssid.length() == 0) {
    Serial.println("No saved Wi-Fi credentials found.");
    return false;
  }

  WiFi.mode(WIFI_STA);
  WiFi.disconnect(true, true);
  delay(200);
  Serial.print("Connecting to Wi-Fi SSID: ");
  Serial.println(deviceConfig.ssid);
  WiFi.begin(deviceConfig.ssid.c_str(), deviceConfig.password.c_str());

  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && (millis() - start) < WIFI_CONNECT_TIMEOUT_MS) {
    delay(500);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Wi-Fi connection failed.");
    return false;
  }

  portalMode = false;
  wifiReady = true;
  Serial.print("Wi-Fi connected. IP: ");
  Serial.println(WiFi.localIP());
  return true;
}

static void startPortalMode() {
  portalMode = true;
  wifiReady = false;
  WiFi.mode(WIFI_AP_STA);
  WiFi.softAP(AP_SSID, AP_PASS);
  dnsServer.start(DNS_PORT, "*", WiFi.softAPIP());
  Serial.print("Config portal started. AP SSID: ");
  Serial.println(AP_SSID);
  Serial.print("Portal IP: ");
  Serial.println(WiFi.softAPIP());
  drawPortalScreen();
}

static bool getNtpTime(struct tm &outTm, uint16_t timeoutMs = 12000) {
  configTime(GMT_OFFSET_SEC, DAYLIGHT_OFFSET_SEC, NTP1, NTP2, NTP3);
  unsigned long start = millis();
  while (millis() - start < timeoutMs) {
    if (getLocalTime(&outTm, 1000)) return true;
    delay(250);
  }
  return false;
}

static bool syncRtcFromNtp() {
  if (WiFi.status() != WL_CONNECTED) return false;

  struct tm timeinfo;
  bool ok = getNtpTime(timeinfo);
  if (ok) {
    DateTime dt(
      timeinfo.tm_year + 1900,
      timeinfo.tm_mon + 1,
      timeinfo.tm_mday,
      timeinfo.tm_hour,
      timeinfo.tm_min,
      timeinfo.tm_sec
    );
    rtc.adjust(dt);
  }
  return ok;
}

static void reportEnvRegistration(bool force) {
  if (!wifiReady || portalMode || WiFi.status() != WL_CONNECTED) {
    return;
  }

  unsigned long now = millis();
  unsigned long minInterval = lastEnvRegistryReportOk
    ? ENV_REGISTRY_REPORT_INTERVAL_MS
    : ENV_REGISTRY_RETRY_INTERVAL_MS;

  if (!force && lastEnvRegistryReportMs != 0 && (now - lastEnvRegistryReportMs) < minInterval) {
    return;
  }

  String serverBaseUrl = getServerBaseUrl();
  String localIp = WiFi.localIP().toString();
  String localBaseUrl = "http://" + localIp;
  String endpointUrl = serverBaseUrl + ENV_REGISTRY_PATH;

  String payload = "{";
  payload += "\"device_id\":\"" + jsonEscape(String(ENV_DEVICE_ID)) + "\",";
  payload += "\"device\":\"fortiroom-main\",";
  payload += "\"hostname\":\"fortiroom-main\",";
  payload += "\"ssid\":\"" + jsonEscape(WiFi.SSID()) + "\",";
  payload += "\"ip\":\"" + jsonEscape(localIp) + "\",";
  payload += "\"base_url\":\"" + jsonEscape(localBaseUrl) + "\",";
  payload += "\"sensors_url\":\"" + jsonEscape(localBaseUrl + "/api/sensors") + "\",";
  payload += "\"health_url\":\"" + jsonEscape(localBaseUrl + "/health") + "\",";
  payload += "\"time_url\":\"" + jsonEscape(localBaseUrl + "/api/time") + "\",";
  payload += "\"unlock_url\":\"" + jsonEscape(localBaseUrl + "/unlock") + "\",";
  payload += "\"door_url\":\"" + jsonEscape(localBaseUrl + "/api/door") + "\",";
  payload += "\"light_url\":\"" + jsonEscape(localBaseUrl + "/light-toggle") + "\",";
  payload += "\"light_state_url\":\"" + jsonEscape(localBaseUrl + "/api/light") + "\",";
  payload += "\"fan_url\":\"" + jsonEscape(localBaseUrl + "/fan") + "\",";
  payload += "\"mac\":\"" + jsonEscape(WiFi.macAddress()) + "\",";
  payload += "\"rssi\":" + String(WiFi.RSSI()) + ",";
  payload += "\"reported_by\":\"esp32\"";
  payload += "}";

  WiFiClient client;
  HTTPClient http;
  http.setTimeout(5000);
  if (!http.begin(client, endpointUrl)) {
    Serial.print("Env registry failed to start HTTP client: ");
    Serial.println(endpointUrl);
    lastEnvRegistryReportMs = now;
    lastEnvRegistryReportOk = false;
    return;
  }

  http.addHeader("Content-Type", "application/json");
  int statusCode = http.POST(payload);
  String responseBody = http.getString();
  http.end();

  lastEnvRegistryReportMs = now;
  lastEnvRegistryReportOk = statusCode >= 200 && statusCode < 300;

  if (lastEnvRegistryReportOk) {
    Serial.print("Env registry updated: ");
    Serial.print(endpointUrl);
    Serial.print(" -> ");
    Serial.println(localBaseUrl);
  } else {
    Serial.print("Env registry update failed (");
    Serial.print(statusCode);
    Serial.print("): ");
    Serial.println(responseBody);
  }
}

static bool syncRtcFromNtpWithLoading(uint32_t minShowMs = 2500) {
  setContrast(0);
  drawLoadingFrame("Syncing Time", "Connecting...", 0, 0.05f, false);
  fade(0, 220);

  uint32_t start = millis();
  uint8_t frame = 0;

  bool ok = syncRtcFromNtp();

  while (millis() - start < minShowMs) {
    float p = 0.35f + 0.65f * ((millis() - start) / (float)minShowMs);
    drawLoadingFrame("Syncing Time", ok ? "Synced" : "Retrying...", frame++, p, false);
    delay(50);
  }

  fade(220, 0);
  return ok;
}

// ---------------- MQ calibration ----------------
static void calibrateMqCleanBaseline() {
  setContrast(0);
  drawLoadingFrame("Calibrating Sensor", "Sampling air...", 0, 0.0f, true);
  fade(0, 220);

  unsigned long start = millis();
  unsigned long lastSample = 0;
  float calEma = NAN;
  double sum = 0.0;
  unsigned long count = 0;
  uint8_t frame = 0;

  while (millis() - start < MQ_CAL_MS) {
    if (millis() - lastSample >= MQ_CAL_SAMPLE_MS) {
      lastSample = millis();
      int raw = analogRead(MQ135_PIN);
      if (isnan(calEma)) calEma = raw;
      calEma = ema(calEma, (float)raw, 0.20f);
      sum += calEma;
      count++;
    }

    float p = (millis() - start) / (float)MQ_CAL_MS;
    drawLoadingFrame("Calibrating Sensor", "Sampling air...", frame++, p, true);
    delay(60);
  }

  int avg = (count > 0) ? (int)((sum / (double)count) + 0.5) : 500;
  gMqClean = avg;
  gMqDirty = max(2500, gMqClean + 1500);

  fade(220, 0);
  Serial.print("MQ CLEAN calibrated to: ");
  Serial.println(gMqClean);
  Serial.print("MQ DIRTY set to: ");
  Serial.println(gMqDirty);
}

// ---------------- Buzzer ----------------
static void updateBuzzer() {
  static bool latched = false;
  bool wasActive = buzzerActive;

  if (!latched && gAirIdx >= AIR_UNHEALTHY_THRESHOLD) latched = true;
  if (latched && gAirIdx < AIR_HYSTERESIS_OFF) latched = false;
  buzzerActive = latched;

  if (buzzerActive != wasActive) {
    syncDoorControl();
    syncFanControl();
  }

  if (!buzzerActive) {
    buzzerOutputOn = false;
    digitalWrite(BUZZER_PIN, LOW);
    return;
  }

  unsigned long now = millis();
  unsigned long interval = buzzerOutputOn ? BUZZ_ON_MS : BUZZ_OFF_MS;

  if (now - buzzerLastToggleMs >= interval) {
    buzzerLastToggleMs = now;
    buzzerOutputOn = !buzzerOutputOn;
    digitalWrite(BUZZER_PIN, buzzerOutputOn ? HIGH : LOW);
  }
}

// ---------------- Sensors ----------------
static void refreshSensorsNow() {
  unsigned long nowMs = millis();
  if (lastDhtRefreshMs == 0 || (nowMs - lastDhtRefreshMs) >= DHT_REFRESH_MS) {
    lastDhtRefreshMs = nowMs;

    float newH = dht.readHumidity();
    float newT = dht.readTemperature();

    if (!isnan(newH) && newH >= 0.0f && newH <= 100.0f) {
      gHum = newH;
    }
    if (!isnan(newT) && newT > -40.0f && newT < 85.0f) {
      gTempC = newT;
    }
  }

  gMqRaw = analogRead(MQ135_PIN);
  if (isnan(gMqEma)) gMqEma = (float)gMqRaw;
  gMqEma = ema(gMqEma, (float)gMqRaw, 0.20f);

  gAirIdx = mq135ToIndexDynamic((int)(gMqEma + 0.5f));
  gAirLabel = aqiLabel(gAirIdx);
  syncFanControl();

  DateTime now = rtc.now();
  Serial.print("[SENS] ");
  print2(Serial, now.day());
  Serial.print("/");
  print2(Serial, now.month());
  Serial.print("/");
  Serial.print(now.year());
  Serial.print(" ");
  print2(Serial, now.hour());
  Serial.print(":");
  print2(Serial, now.minute());
  Serial.print(":");
  print2(Serial, now.second());
  Serial.print(" | T=");
  if (isnan(gTempC)) Serial.print("NA"); else Serial.print(gTempC, 1);
  Serial.print("C H=");
  if (isnan(gHum)) Serial.print("NA"); else Serial.print(gHum, 0);
  Serial.print("% | MQraw=");
  Serial.print(gMqRaw);
  Serial.print(" MQema=");
  Serial.print((int)(gMqEma + 0.5f));
  Serial.print(" CLEAN=");
  Serial.print(gMqClean);
  Serial.print(" AirIdx=");
  Serial.print(gAirIdx);
  Serial.print(" ");
  Serial.println(gAirLabel);
}

static void showTransientStatus(const String &line1, const String &line2, const String &line3, unsigned long durationMs) {
  statusMessageLine1 = line1;
  statusMessageLine2 = line2;
  statusMessageLine3 = line3;
  statusMessageUntilMs = millis() + durationMs;
}

static String currentBookingWindowKey() {
  if (gBookingStatus.windowKey.length() > 0) {
    return gBookingStatus.windowKey;
  }
  if (gBookingStatus.primaryUsername.length() == 0 && gBookingStatus.displayName.length() == 0) {
    return "";
  }
  return gBookingStatus.relevantState + "|" + gBookingStatus.primaryUsername + "|" + gBookingStatus.displayName;
}

static void syncBookingClimatePolicy() {
  if (gBookingStatus.relevantState == "active") {
    if (!gBookingStatus.verifiedThisWindow) {
      applyLightOutput(false);
    }
    if (gFanMode == 'A' && gFanRequestedSpeed != 3) {
      gFanMode = 'A';
      gFanRequestedSpeed = 3;
      syncFanControl();
    }
    return;
  }

  if (gBookingStatus.relevantState == "idle") {
    if (gLightOn) {
      applyLightOutput(false);
    }
    if (gFanMode != 'A' || gFanRequestedSpeed != 0) {
      gFanMode = 'A';
      gFanRequestedSpeed = 0;
      syncFanControl();
    }
  }
}

static bool fetchBookingStatus() {
  if (!wifiReady || portalMode || WiFi.status() != WL_CONNECTED) return false;

  String url = getServerBaseUrl() + BOOKING_STATUS_PATH + "?pod_id=1";
  String host = "10.150.215.215";
  String path = String("/Fortiroom") + BOOKING_STATUS_PATH + "?pod_id=1";
  Serial.print("Fetching booking status: ");
  Serial.println(url);
  Serial.print("ESP WiFi IP=");
  Serial.print(WiFi.localIP());
  Serial.print(" gateway=");
  Serial.print(WiFi.gatewayIP());
  Serial.print(" RSSI=");
  Serial.println(WiFi.RSSI());
  bool tcpReachable = false;
  for (int attempt = 1; attempt <= 3; attempt++) {
    WiFiClient probeClient;
    probeClient.setTimeout(WIFI_TCP_CONNECT_TIMEOUT_MS / 1000);
    Serial.print("Booking status TCP probe attempt ");
    Serial.print(attempt);
    Serial.print("/3...");
    if (probeClient.connect(host.c_str(), 80, WIFI_TCP_CONNECT_TIMEOUT_MS)) {
      Serial.println(" OK");
      tcpReachable = true;
      probeClient.stop();
      break;
    }
    Serial.println(" failed");
    probeClient.stop();
    delay(250);
  }
  if (!tcpReachable) {
    Serial.println("Booking status TCP probe failed: cannot connect to 172.20.10.13:80 after retries");
    return false;
  }

  WiFiClient client;
  client.setTimeout(15000);
  if (!client.connect(host.c_str(), 80, WIFI_TCP_CONNECT_TIMEOUT_MS)) {
    Serial.println("Booking status raw HTTP connect failed");
    return false;
  }

  client.print(String("GET ") + path + " HTTP/1.1\r\n");
  client.print(String("Host: ") + host + "\r\n");
  client.print("Accept: application/json\r\n");
  client.print("Connection: close\r\n\r\n");

  unsigned long startMs = millis();
  while (!client.available() && (millis() - startMs) < 15000) {
    delay(10);
  }

  String response = "";
  while (client.connected() || client.available()) {
    while (client.available()) {
      response += (char)client.read();
      if (response.length() > 12000) {
        break;
      }
    }
    if (response.length() > 12000) {
      break;
    }
    delay(1);
  }
  client.stop();

  int statusCode = 0;
  int statusPos = response.indexOf("HTTP/");
  if (statusPos >= 0) {
    int firstSpace = response.indexOf(' ', statusPos);
    if (firstSpace > 0 && firstSpace + 4 <= (int)response.length()) {
      statusCode = response.substring(firstSpace + 1, firstSpace + 4).toInt();
    }
  }

  int bodyStart = response.indexOf("\r\n\r\n");
  String body = bodyStart >= 0 ? response.substring(bodyStart + 4) : "";

  if (statusCode < 200 || statusCode >= 300) {
    Serial.print("Booking status fetch failed (");
    Serial.print(statusCode);
    Serial.print("): ");
    Serial.println(body);
    return false;
  }

  gBookingStatus.bookingsTodayCount = jsonExtractInt(body, "bookings_today_count", 0);
  gBookingStatus.relevantState = jsonExtractString(body, "relevant_state", "idle");
  gBookingStatus.verificationWindowOpen = jsonExtractBool(body, "verification_window_open", false);
  gBookingStatus.primaryUsername = jsonExtractString(body, "primary_username", "");
  gBookingStatus.secondaryUsername = jsonExtractString(body, "secondary_username", "");
  gBookingStatus.displayName = jsonExtractString(body, "display_name", "");
  gBookingStatus.windowKey = jsonExtractString(body, "window_key", "");
  gBookingStatus.cameraOnline = jsonExtractBool(body, "online", false);
  gBookingStatus.cameraButtonUrl = jsonExtractString(body, "button_url", "");
  gBookingStatus.cameraTriggerCaptureUrl = jsonExtractString(body, "trigger_capture_url", "");
  gBookingStatus.cameraCaptureUrl = jsonExtractString(body, "capture_url", "");
  gBookingStatus.cameraBaseUrl = jsonExtractString(body, "base_url", "");

  String newWindowKey = currentBookingWindowKey();
  if (newWindowKey != gLastBookingWindowKey) {
    gBookingStatus.verifiedThisWindow = false;
    gBookingStatus.verifiedBookingKey = "";
    gLastBookingWindowKey = newWindowKey;
  }

  syncBookingClimatePolicy();
  return true;
}

static bool pollCameraButton(String &triggerCaptureUrl) {
  triggerCaptureUrl = "";
  if (!wifiReady || portalMode || WiFi.status() != WL_CONNECTED) return false;
  if (!gBookingStatus.verificationWindowOpen) return false;
  if (!gBookingStatus.cameraOnline) return false;
  if (gBookingStatus.cameraButtonUrl.length() == 0) return false;
  if (gBookingStatus.verificationInProgress) return false;

  WiFiClient client;
  HTTPClient http;
  http.setTimeout(2500);
  if (!http.begin(client, gBookingStatus.cameraButtonUrl)) {
    return false;
  }

  int statusCode = http.GET();
  String body = http.getString();
  http.end();

  if (statusCode < 200 || statusCode >= 300) {
    return false;
  }

  bool pressed = jsonExtractBool(body, "pressed", false);
  if (!pressed) {
    return false;
  }

  triggerCaptureUrl = jsonExtractString(body, "capture_url", "");
  if (triggerCaptureUrl.length() == 0) {
    triggerCaptureUrl = gBookingStatus.cameraTriggerCaptureUrl;
  }
  return triggerCaptureUrl.length() > 0;
}

static bool verifyCurrentBookingFace(const String &captureUrl) {
  if (!wifiReady || portalMode || WiFi.status() != WL_CONNECTED) return false;
  if (gBookingStatus.primaryUsername.length() == 0) return false;
  if (gBookingStatus.cameraBaseUrl.length() == 0) return false;

  String url = gBookingStatus.cameraBaseUrl + "/verify-booking";
  String payload = "{";
  payload += "\"username\":\"" + jsonEscape(gBookingStatus.primaryUsername) + "\",";
  payload += "\"esp32_capture_url\":\"" + jsonEscape(captureUrl) + "\"";
  payload += "}";

  WiFiClient client;
  HTTPClient http;
  http.setTimeout(20000);
  if (!http.begin(client, url)) {
    return false;
  }

  http.addHeader("Content-Type", "application/json");
  int statusCode = http.POST(payload);
  String body = http.getString();
  http.end();

  if (statusCode < 200 || statusCode >= 300) {
    Serial.print("Face verify failed (");
    Serial.print(statusCode);
    Serial.print("): ");
    Serial.println(body);
    return false;
  }
  bool verified = jsonExtractBool(body, "verified", false);
  Serial.print("Face verify main response (");
  Serial.print(statusCode);
  Serial.print(") parsed verified=");
  Serial.print(verified ? "true" : "false");
  Serial.print(" body=");
  Serial.println(body);
  return verified;
}

static bool verifyBookingUserFace(const String &username, const String &captureUrl) {
  if (!wifiReady || portalMode || WiFi.status() != WL_CONNECTED) return false;
  if (username.length() == 0) return false;
  if (gBookingStatus.cameraBaseUrl.length() == 0) return false;

  String url = gBookingStatus.cameraBaseUrl + "/verify-booking";
  String payload = "{";
  payload += "\"username\":\"" + jsonEscape(username) + "\",";
  payload += "\"esp32_capture_url\":\"" + jsonEscape(captureUrl) + "\"";
  payload += "}";

  WiFiClient client;
  HTTPClient http;
  http.setTimeout(20000);
  if (!http.begin(client, url)) {
    return false;
  }

  http.addHeader("Content-Type", "application/json");
  int statusCode = http.POST(payload);
  String body = http.getString();
  http.end();

  if (statusCode < 200 || statusCode >= 300) {
    Serial.print("Face verify failed for user ");
    Serial.print(username);
    Serial.print(" (");
    Serial.print(statusCode);
    Serial.print("): ");
    Serial.println(body);
    return false;
  }

  bool verified = jsonExtractBool(body, "verified", false);
  Serial.print("Face verify main response for user ");
  Serial.print(username);
  Serial.print(" (");
  Serial.print(statusCode);
  Serial.print(") parsed verified=");
  Serial.print(verified ? "true" : "false");
  Serial.print(" body=");
  Serial.println(body);
  return verified;
}

static void verificationTaskEntry(void *parameter) {
  String captureUrl = parameter != nullptr ? *((String *)parameter) : String("");
  if (parameter != nullptr) {
    delete ((String *)parameter);
  }

  bool verified = verifyBookingUserFace(gBookingStatus.primaryUsername, captureUrl);
  String matchedUsername = "";
  String matchedRole = "";
  if (verified) {
    matchedUsername = gBookingStatus.primaryUsername;
    matchedRole = "Primary User";
  }
  if (!verified && gBookingStatus.secondaryUsername.length() > 0) {
    Serial.print("Primary verification failed, trying secondary user: ");
    Serial.println(gBookingStatus.secondaryUsername);
    verified = verifyBookingUserFace(gBookingStatus.secondaryUsername, captureUrl);
    if (verified) {
      matchedUsername = gBookingStatus.secondaryUsername;
      matchedRole = "Secondary User";
    }
  }
  gVerificationResultVerified = verified;
  gVerificationMatchedUsername = matchedUsername;
  gVerificationMatchedRole = matchedRole;
  gVerificationResultReady = true;
  gVerificationTaskRunning = false;
  gVerificationTaskHandle = nullptr;
  vTaskDelete(nullptr);
}

static void startVerificationTask(const String &captureUrl) {
  if (gVerificationTaskRunning) {
    return;
  }

  gVerificationTaskRunning = true;
  gVerificationResultReady = false;
  gVerificationResultVerified = false;
  String *taskArg = new String(captureUrl);
  BaseType_t taskOk = xTaskCreatePinnedToCore(
    verificationTaskEntry,
    "verify_face",
    8192,
    taskArg,
    1,
    &gVerificationTaskHandle,
    1
  );

  if (taskOk != pdPASS) {
    gVerificationTaskRunning = false;
    gVerificationTaskHandle = nullptr;
    delete taskArg;
    showTransientStatus("Verification", "Failed to start", "", 3000);
  }
}

static void handleVerificationResult() {
  if (!gVerificationResultReady) {
    return;
  }

  bool verified = gVerificationResultVerified;
  gVerificationResultReady = false;
  gBookingStatus.verificationInProgress = false;
  Serial.print("Verification result ready on main ESP32: ");
  Serial.println(verified ? "VERIFIED" : "UNVERIFIED");

  if (!verified) {
    showTransientStatus("Unverified", "Please try again", "", 3500);
    return;
  }

  gBookingStatus.verifiedThisWindow = true;
  gBookingStatus.verifiedBookingKey = currentBookingWindowKey();
  String verifiedLine = "Verified";
  if (gVerificationMatchedRole.length() > 0) {
    verifiedLine = "Verified: " + gVerificationMatchedRole;
  }
  String matchedUserLine = gVerificationMatchedUsername;
  if (matchedUserLine.length() > 20) {
    matchedUserLine = matchedUserLine.substring(0, 20);
  }
  showTransientStatus(verifiedLine, matchedUserLine.length() > 0 ? matchedUserLine : "Door Lock opening", matchedUserLine.length() > 0 ? "Door Lock opening" : "", 3500);
  startDoorUnlock();
  applyLightOutput(true);
  gFanMode = 'A';
  gFanRequestedSpeed = 3;
  syncFanControl();
}

static void handleVerificationFlow() {
  if (!gBookingStatus.verificationWindowOpen) return;
  if (gBookingStatus.primaryUsername.length() == 0) return;
  if (gBookingStatus.verifiedThisWindow) return;
  if (gBookingStatus.verificationInProgress || gVerificationTaskRunning) return;

  String triggerCaptureUrl;
  if (!pollCameraButton(triggerCaptureUrl)) {
    return;
  }

  gBookingStatus.verificationInProgress = true;
  gVerificationRequestUsername = gBookingStatus.primaryUsername;
  showTransientStatus("Verifying...", gVerificationRequestUsername, "Please wait", 30000);
  startVerificationTask(triggerCaptureUrl);
}

static bool reportCheckoutMotionPenalty() {
  if (!wifiReady || portalMode || WiFi.status() != WL_CONNECTED) return false;
  if (gCheckoutBookingId.length() == 0 || gCheckoutPrimaryUserId.length() == 0) return false;

  String url = getServerBaseUrl() + "/checkout_motion_penalty.php";
  String payload = "{";
  payload += "\"booking_id\":\"" + jsonEscape(gCheckoutBookingId) + "\",";
  payload += "\"pod_id\":\"" + jsonEscape(gCheckoutPodId) + "\",";
  payload += "\"primary_user_id\":\"" + jsonEscape(gCheckoutPrimaryUserId) + "\",";
  payload += "\"secondary_user_id\":\"" + jsonEscape(gCheckoutSecondaryUserId) + "\",";
  payload += "\"motion_detected\":true,";
  payload += "\"device_id\":\"" + jsonEscape(String(ENV_DEVICE_ID)) + "\"";
  payload += "}";

  WiFiClient client;
  HTTPClient http;
  http.setTimeout(10000);
  if (!http.begin(client, url)) {
    return false;
  }
  http.addHeader("Content-Type", "application/json");
  int statusCode = http.POST(payload);
  String body = http.getString();
  http.end();

  Serial.print("Checkout motion penalty report (");
  Serial.print(statusCode);
  Serial.print("): ");
  Serial.println(body);
  return statusCode >= 200 && statusCode < 300;
}

static void handleCheckoutMotionMonitor() {
  if (!gCheckoutMonitorActive) return;

  unsigned long elapsedMs = millis() - gCheckoutMonitorStartedMs;
  if (elapsedMs >= CHECKOUT_MOTION_MONITOR_MS || gBookingStatus.relevantState == "idle") {
    gCheckoutMonitorActive = false;
    return;
  }

  if (elapsedMs < CHECKOUT_MOTION_ARM_DELAY_MS) return;
  if (gCheckoutPenaltyReported) return;

  if (digitalRead(PIR_PIN) == HIGH) {
    gCheckoutPenaltyReported = true;
    showTransientStatus("Motion Detected", "After Checkout", "Penalty Added", 6000);
    if (reportCheckoutMotionPenalty()) {
      gCheckoutMonitorActive = false;
    }
  }
}

// ---------------- Setup / Loop ----------------
void setup() {
  // Drive outputs to a safe default as early as possible during boot.
  pinMode(LIGHT_PIN, OUTPUT);
  applyLightOutput(false);
  pinMode(DOOR_RELAY_PIN, OUTPUT);
  applyDoorRelay(false);
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  pinMode(PIR_PIN, INPUT);

  Serial.begin(115200);
  delay(200);
  WiFi.setSleep(false);

  initFanPwm();
  applyFanOutput(resolveFanSpeed());

  I2C_RTC.begin(RTC_SDA, RTC_SCL);
  I2C_OLED.begin(OLED_SDA, OLED_SCL);

  if (!rtc.begin(&I2C_RTC)) {
    Serial.println("RTC not found!");
    while (1) delay(10);
  }

  if (!display.begin(OLED_ADDR, true)) {
    Serial.println("SH110X OLED not found! Try OLED_ADDR 0x3D");
    while (1) delay(10);
  }

  dht.begin();
  loadConfig();
  registerRoutes();

  setContrast(0);
  showTitle();
  fade(0, 220);
  delay(800);
  fade(220, 0);

  showTagline();
  fade(0, 220);
  delay(1200);
  fade(220, 0);

  if (!connectToSavedWiFi()) {
    startPortalMode();
  } else {
    ntpSyncedThisBoot = syncRtcFromNtpWithLoading(2500);
  }

  calibrateMqCleanBaseline();
  refreshSensorsNow();
  lastSensorRefreshMs = millis();
  lastOledRefreshMs = millis();

  if (!portalMode) {
    setContrast(0);
    drawEnvScreen(rtc.now(), ntpSyncedThisBoot);
    fade(0, 220);
    reportEnvRegistration(true);
    Serial.print("API sensors: http://");
    Serial.print(WiFi.localIP());
    Serial.println("/api/sensors");
  }

  server.begin();
}

void loop() {
  server.handleClient();

  if (portalMode) {
    dnsServer.processNextRequest();
    drawPortalScreen();
    delay(20);
    return;
  }

  unsigned long nowMs = millis();

  if (nowMs - lastNtpSyncMs >= NTP_RESYNC_MS) {
    bool ok = syncRtcFromNtp();
    if (ok) ntpSyncedThisBoot = true;
    lastNtpSyncMs = nowMs;
    Serial.println(ok ? "NTP re-sync saved to RTC" : "NTP re-sync failed, keep RTC");
  }

  reportEnvRegistration(false);
  if (lastBookingPollMs == 0 || (nowMs - lastBookingPollMs) >= BOOKING_POLL_MS) {
    lastBookingPollMs = nowMs;
    fetchBookingStatus();
  }
  updateDoorRelay();
  updateFanOutput();

  if (nowMs - lastSensorRefreshMs >= SENSOR_REFRESH_MS) {
    lastSensorRefreshMs = nowMs;
    refreshSensorsNow();
  }

  updateBuzzer();
  if (lastCameraButtonPollMs == 0 || (nowMs - lastCameraButtonPollMs) >= CAMERA_BUTTON_POLL_MS) {
    lastCameraButtonPollMs = nowMs;
    handleVerificationFlow();
  }
  handleVerificationResult();
  handleCheckoutMotionMonitor();

  if (nowMs - lastOledRefreshMs >= OLED_REFRESH_MS) {
    lastOledRefreshMs = nowMs;
    drawEnvScreen(rtc.now(), ntpSyncedThisBoot);
  }

  delay(5);
}
