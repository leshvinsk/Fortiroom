#include <WiFi.h>
#include <WebServer.h>
#include <time.h>
#include <Wire.h>
#include "RTClib.h"
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <DHT.h>
#include <math.h>

/*
WIRING REFERENCE (ESP32):

1) DS3231 RTC
   - VCC -> 3V3
   - GND -> GND
   - SDA -> GPIO32
   - SCL -> GPIO33

2) OLED SSD1306 (I2C)
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
*/

// ---------------- PIN CONFIG ----------------
#define RTC_SDA   32
#define RTC_SCL   33

#define OLED_SDA  25
#define OLED_SCL  26

#define DHT_PIN   27
#define DHT_TYPE  DHT22

#define MQ135_PIN 34

#define BUZZER_PIN 18   // 2-pin active buzzer (+) -> GPIO18, (-) -> GND

// ---------------- OLED CONFIG ----------------
#define SCREEN_WIDTH  128
#define SCREEN_HEIGHT  64
#define OLED_RESET     -1
#define OLED_ADDR      0x3C  // change to 0x3D if needed

// ---------------- WIFI / NTP ----------------
const char* WIFI_SSID = "Guest@HELP";
const char* WIFI_PASS = "guEST@HELP";

const long  GMT_OFFSET_SEC = 8 * 3600; // Malaysia UTC+8
const int   DAYLIGHT_OFFSET_SEC = 0;

const char* NTP1 = "pool.ntp.org";
const char* NTP2 = "time.google.com";
const char* NTP3 = "time.cloudflare.com";

const unsigned long NTP_RESYNC_MS = 6UL * 60UL * 60UL * 1000UL;

// ---------------- Refresh rates ----------------
const unsigned long SENSOR_REFRESH_MS = 1000;  // every 1s
const unsigned long OLED_REFRESH_MS   = 1000;  // every 1s

// ---------------- AQI Display Realism ----------------
const int BASELINE_AIRIDX = 60;   // Malaysia-like "base" shown even in clean conditions
const int AIRIDX_MAX      = 500;

// ---------------- AQ Alarm ----------------
const int AIR_UNHEALTHY_THRESHOLD = 101;  // ON when > 101
const int AIR_HYSTERESIS_OFF      = 101;  // OFF when < 101

// Louder / more persistent pattern (active buzzer)
// NOTE: "loudness" is hardware; software makes it more persistent.
const unsigned long BUZZ_ON_MS  = 700;
const unsigned long BUZZ_OFF_MS = 200;

// ---------------- MQ Auto Calibration ----------------
const unsigned long MQ_CAL_MS = 10000;        // first 10 seconds
const unsigned long MQ_CAL_SAMPLE_MS = 100;   // sample every 100ms

// ---------------- I2C BUSES ----------------
TwoWire I2C_RTC  = TwoWire(0);
TwoWire I2C_OLED = TwoWire(1);

RTC_DS3231 rtc;
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &I2C_OLED, OLED_RESET);
DHT dht(DHT_PIN, DHT_TYPE);

// ---------------- Web Server ----------------
WebServer server(80);

// ---------------- Helpers ----------------
static void print2(Print &p, int v) { if (v < 10) p.print('0'); p.print(v); }

static inline float clampf(float x, float lo, float hi) {
  if (x < lo) return lo;
  if (x > hi) return hi;
  return x;
}

static float ema(float prev, float current, float alpha) {
  return prev + alpha * (current - prev);
}

static const char* aqiLabel(int idx) {
  if (idx <= 50)  return "Good";
  if (idx <= 100) return "Moderate";
  if (idx <= 150) return "Unhealthy(SG)";
  if (idx <= 200) return "Unhealthy";
  if (idx <= 300) return "Very Unhealthy";
  return "Hazardous";
}

// ---------------- Live global values ----------------
static bool ntpSyncedThisBoot = false;
static unsigned long lastNtpSyncMs = 0;

static unsigned long lastSensorRefreshMs = 0;
static unsigned long lastOledRefreshMs = 0;

static float gTempC = NAN;
static float gHum   = NAN;

static int   gMqRaw = 0;
static float gMqEma = NAN;

static int   gAirIdx = 0;
static const char* gAirLabel = "Good";

// Dynamic MQ calibration points
static int gMqClean = 500;   // overwritten by auto-calibration
static int gMqDirty = 2500;  // derived from clean (or fixed minimum)

// Buzzer state
static bool buzzerActive = false;        // derived (with hysteresis)
static bool buzzerOutputOn = false;      // pin state
static unsigned long buzzerLastToggleMs = 0;

// ---------------- MQ mapping using dynamic CLEAN/DIRTY + baseline floor ----------------
static int mq135ToIndexDynamic(int raw) {
  int clean = gMqClean;
  int dirty = gMqDirty;

  if (dirty <= clean + 50) dirty = clean + 50;

  // Realism: if at/below clean baseline, show Malaysia-like baseline (not 0)
  if (raw <= clean) return BASELINE_AIRIDX;

  // Scale from BASELINE_AIRIDX up to 500 as air worsens
  float norm = (raw - clean) / (float)(dirty - clean);
  norm = clampf(norm, 0.0f, 1.0f);

  int idx = BASELINE_AIRIDX + (int)(norm * (AIRIDX_MAX - BASELINE_AIRIDX) + 0.5f);
  if (idx > AIRIDX_MAX) idx = AIRIDX_MAX;
  return idx;
}

// ---------------- OLED Fade UI ----------------
static void setContrast(uint8_t c) {
  display.ssd1306_command(SSD1306_SETCONTRAST);
  display.ssd1306_command(c);
}

static void fade(uint8_t fromC, uint8_t toC, int stepDelay = 4) {
  if (fromC < toC) {
    for (int c = fromC; c <= toC; c += 3) { setContrast((uint8_t)c); delay(stepDelay); }
  } else {
    for (int c = fromC; c >= toC; c -= 3) { setContrast((uint8_t)c); delay(stepDelay); }
  }
  setContrast(toC);
}

static void showTitle() {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(2);

  int16_t x1, y1; uint16_t w, h;
  display.getTextBounds("FORTIROOM", 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 22);
  display.println("FORTIROOM");
  display.display();
}

static void showTagline() {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(1);

  int16_t x1, y1; uint16_t w, h;

  display.getTextBounds("Smart", 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 16); display.println("Smart");

  display.getTextBounds("Secure", 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 28); display.println("Secure");

  display.getTextBounds("Seamless", 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 40); display.println("Seamless");

  display.display();
}

static void drawLoadingFrame(const char* message, const char* phase, uint8_t frame, float progress01, bool showPercent) {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(1);

  int16_t x1, y1; uint16_t w, h;
  display.getTextBounds(message, 0, 0, &x1, &y1, &w, &h);
  display.setCursor((128 - w) / 2, 10);
  display.println(message);

  if (phase && strlen(phase) > 0) {
    display.getTextBounds(phase, 0, 0, &x1, &y1, &w, &h);
    display.setCursor((128 - w) / 2, 22);
    display.println(phase);
  }

  // Spinner
  const int cx = 64, cy = 38, r = 7;
  for (int i = 0; i < 8; i++) {
    float a = (frame + i) * 0.785398f; // pi/4
    int x = cx + (int)(cos(a) * r);
    int y = cy + (int)(sin(a) * r);
    if (i == 0 || i == 1) display.fillCircle(x, y, 1, SSD1306_WHITE);
    else display.drawPixel(x, y, SSD1306_WHITE);
  }

  // Progress bar
  progress01 = clampf(progress01, 0.0f, 1.0f);
  const int barX = 14, barY = 52, barW = 100, barH = 8;
  display.drawRoundRect(barX, barY, barW, barH, 3, SSD1306_WHITE);
  int fillW = (int)((barW - 2) * progress01 + 0.5f);
  if (fillW > 0) display.fillRoundRect(barX + 1, barY + 1, fillW, barH - 2, 2, SSD1306_WHITE);

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
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 2);
  display.print("Time  ");
  print2(display, now.hour()); display.print(":");
  print2(display, now.minute()); display.print(":");
  print2(display, now.second()); 
  display.setCursor(104, 2);
  display.print(ntpOk ? "NTP" : "RTC");

  display.setCursor(0, 20);
  if (isnan(gTempC) || isnan(gHum)) {
    display.println("Temp  --.- C");
    display.setCursor(0, 34);
    display.println("Hum   -- %");
  } else {
    display.print("Temp  "); display.print(gTempC, 1); display.println(" C");
    display.setCursor(0, 34);
    display.print("Hum   ");  display.print(gHum, 0);  display.println(" %");
  }

  display.setCursor(0, 48);
  display.print("AQI   ");
  display.print(gAirIdx);
  display.print("  ");
  display.println(gAirLabel);

  display.display();
}

// ---------------- Time Sync Logic ----------------
static bool connectWiFi(uint16_t timeoutMs = 15000) {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < timeoutMs) {
    delay(250);
  }
  return WiFi.status() == WL_CONNECTED;
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
  if (strlen(WIFI_SSID) == 0) return false;
  if (!connectWiFi()) return false;

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

static bool syncRtcFromNtpWithLoading(uint32_t minShowMs = 2500) {
  setContrast(0);
  drawLoadingFrame("Syncing Time", "Connecting...", 0, 0.05f, false);
  fade(0, 220);

  uint32_t start = millis();
  uint8_t frame = 0;
  uint32_t preAnimStart = millis();
  while (millis() - preAnimStart < 900) {
    float p = 0.10f + 0.20f * ((millis() - preAnimStart) / 900.0f);
    drawLoadingFrame("Syncing Time", "Connecting...", frame++, p, false);
    delay(50);
  }

  bool ok = syncRtcFromNtp();

  while (millis() - start < minShowMs) {
    float p = 0.35f + 0.65f * ((millis() - start) / (float)minShowMs);
    drawLoadingFrame("Syncing Time", ok ? "Synced" : "Retrying...", frame++, p, false);
    delay(50);
  }

  fade(220, 0);
  return ok;
}

// ---------------- MQ Auto Calibration ----------------
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

  Serial.print("✅ MQ CLEAN calibrated to: ");
  Serial.println(gMqClean);
  Serial.print("✅ MQ DIRTY set to: ");
  Serial.println(gMqDirty);
}

// ---------------- Buzzer control (non-blocking) + hysteresis ----------------
static void updateBuzzer() {
  // Single cut-over behavior requested:
  // ON above 101, OFF below 101.
  static bool latched = false;

  if (!latched && gAirIdx > AIR_UNHEALTHY_THRESHOLD) latched = true;
  if (latched && gAirIdx < AIR_HYSTERESIS_OFF) latched = false;

  buzzerActive = latched;

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

// ---------------- Web API ----------------
static String jsonSafeFloat(float v, int decimals) {
  if (isnan(v)) return String("null");
  return String(v, decimals);
}

static void handleRoot() {
  String msg = "FORTIROOM API\n";
  msg += "Use /api/time and /api/sensors";
  server.send(200, "text/plain", msg);
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
  json += "\"ip\":\"" + WiFi.localIP().toString() + "\"";
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
  json += "\"buzzer_active\":" + String(buzzerActive ? "true" : "false");
  json += "}";
  server.send(200, "application/json", json);
}

static void setupWebServer() {
  server.on("/", handleRoot);
  server.on("/api/time", handleApiTime);
  server.on("/api/sensors", handleApiSensors);
  server.begin();
}

// ---------------- Sensor Refresh ----------------
static void refreshSensorsNow() {
  float newH = dht.readHumidity();
  float newT = dht.readTemperature();
  if (!isnan(newH) && !isnan(newT)) {
    gHum = newH;
    gTempC = newT;
  }

  gMqRaw = analogRead(MQ135_PIN);
  if (isnan(gMqEma)) gMqEma = (float)gMqRaw;
  gMqEma = ema(gMqEma, (float)gMqRaw, 0.20f);

  gAirIdx = mq135ToIndexDynamic((int)(gMqEma + 0.5f));
  gAirLabel = aqiLabel(gAirIdx);

  DateTime now = rtc.now();
  Serial.print("[SENS] ");
  print2(Serial, now.day()); Serial.print("/");
  print2(Serial, now.month()); Serial.print("/");
  Serial.print(now.year()); Serial.print(" ");
  print2(Serial, now.hour()); Serial.print(":");
  print2(Serial, now.minute()); Serial.print(":");
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

// ---------------- Setup / Loop ----------------
void setup() {
  Serial.begin(115200);
  delay(200);

  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  I2C_RTC.begin(RTC_SDA, RTC_SCL);
  I2C_OLED.begin(OLED_SDA, OLED_SCL);

  if (!rtc.begin(&I2C_RTC)) {
    Serial.println("❌ RTC not found!");
    while (1) delay(10);
  }

  if (!display.begin(SSD1306_SWITCHCAPVCC, OLED_ADDR)) {
    Serial.println("❌ OLED not found! Try OLED_ADDR 0x3D");
    while (1) delay(10);
  }

  dht.begin();

  setContrast(0);

  showTitle();
  fade(0, 220);
  delay(800);
  fade(220, 0);

  showTagline();
  fade(0, 220);
  delay(1200);
  fade(220, 0);

  ntpSyncedThisBoot = syncRtcFromNtpWithLoading(2500);

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi not connected after NTP sync, trying again...");
    connectWiFi(15000);
  }

  Serial.print("✅ WiFi: ");
  Serial.println(WiFi.status() == WL_CONNECTED ? "CONNECTED" : "NOT CONNECTED");
  Serial.print("✅ IP Address: ");
  Serial.println(WiFi.localIP());

  calibrateMqCleanBaseline();

  setupWebServer();

  refreshSensorsNow();
  lastSensorRefreshMs = millis();
  lastOledRefreshMs = millis();

  setContrast(0);
  DateTime now = rtc.now();
  drawEnvScreen(now, ntpSyncedThisBoot);
  fade(0, 220);

  Serial.println("API endpoints:");
  Serial.print("http://");
  Serial.print(WiFi.localIP());
  Serial.println("/api/sensors");
}

void loop() {
  server.handleClient();

  unsigned long nowMs = millis();

  if (nowMs - lastNtpSyncMs >= NTP_RESYNC_MS) {
    bool ok = syncRtcFromNtp();
    if (ok) ntpSyncedThisBoot = true;
    lastNtpSyncMs = nowMs;
    Serial.println(ok ? "✅ NTP re-sync saved to RTC" : "⚠ NTP re-sync failed, keep RTC");
    if (WiFi.status() != WL_CONNECTED) connectWiFi(15000);
  }

  if (nowMs - lastSensorRefreshMs >= SENSOR_REFRESH_MS) {
    lastSensorRefreshMs = nowMs;
    refreshSensorsNow();
  }

  updateBuzzer();

  if (nowMs - lastOledRefreshMs >= OLED_REFRESH_MS) {
    lastOledRefreshMs = nowMs;
    DateTime now = rtc.now();
    drawEnvScreen(now, ntpSyncedThisBoot);
  }

  delay(5);
}
