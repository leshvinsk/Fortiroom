#include "esp_camera.h"
#include <WiFi.h>
#include <WebServer.h>
#include <Preferences.h>
#include <WiFiClient.h>
#include <WiFiServer.h>
#include <ArduinoOTA.h>
#include <Update.h>
#include <HTTPClient.h>
#include <ESPmDNS.h>
#include <DNSServer.h>
#include <stdarg.h>
#include <esp_arduino_version.h>

#ifndef pin_sccb_sda
#define pin_sccb_sda pin_sscb_sda
#endif

#ifndef pin_sccb_scl
#define pin_sccb_scl pin_sscb_scl
#endif

/*
  FORTIROOM ESP32-CAM Wireless Capture Server

  Features:
  - Wi-Fi auto-connect using saved credentials in NVS
  - AP fallback + captive-style configuration portal
  - ArduinoOTA support for Arduino IDE network upload
  - Web OTA upload page at /update
  - Telnet logging on port 23
  - Web log viewer at /logs
  - Existing camera endpoints preserved:
      GET /capture
      GET /button
      GET /trigger-capture
      GET /health
      GET /
*/

#define TRIGGER_PIN 3
#define FLASH_LED_PIN 4
#define FLASH_LED_PWM_CHANNEL 7
#define FLASH_LED_PWM_FREQ 5000
#define FLASH_LED_PWM_RESOLUTION 8
#define FLASH_LED_BRIGHTNESS 108

const unsigned long TRIGGER_DEBOUNCE_MS = 250;
const unsigned long TRIGGER_COOLDOWN_MS = 1200;
const unsigned long PORTAL_BLINK_INTERVAL_MS = 2000;
const char* AP_SSID = "FORTIROOM-CAM-Setup";
const char* AP_PASS = "";
const byte DNS_PORT = 53;
const unsigned long WIFI_CONNECT_TIMEOUT_MS = 20000;
const char* PREF_NAMESPACE = "fortiroom";
const bool USE_HARDCODED_WIFI = true;
const char* HARDCODED_WIFI_SSID = "MYSEP-Student";
const char* HARDCODED_WIFI_PASS = "Tenby2018";
const uint16_t TELNET_PORT = 23;
const size_t TELNET_CLIENT_SLOTS = 2;
const size_t LOG_BUFFER_LINES = 120;
const size_t WIFI_SCAN_CACHE_LIMIT = 20;
// Change this when your XAMPP/laptop IP changes.
// Example: http://10.150.211.78/Fortiroom
const char* DEFAULT_SERVER_BASE_URL = "http://10.3.40.216/Fortiroom";
const char* CAMERA_REGISTRY_PATH = "/esp32_cam_registry.php";
const char* FACE_VERIFY_PATH = "/face_verify_api.php";
const char* CAMERA_DEVICE_ID = "fortiroom-cam";
const unsigned long CAMERA_REGISTRY_REPORT_INTERVAL_MS = 60000;
const unsigned long CAMERA_REGISTRY_RETRY_INTERVAL_MS = 15000;
const unsigned long VERIFY_TIMEOUT_MS = 20000;

WebServer server(80);
WiFiServer telnetServer(TELNET_PORT);
WiFiClient telnetClients[TELNET_CLIENT_SLOTS];
Preferences prefs;
DNSServer dnsServer;

bool portalMode = false;
bool wifiReady = false;
String deviceHostname = "fortiroom-cam";
String savedServerBase = "";
unsigned long lastPortalBlinkMs = 0;
unsigned long lastCameraRegistryReportMs = 0;
bool lastCameraRegistryReportOk = false;
String cachedSsids[WIFI_SCAN_CACHE_LIMIT];
int32_t cachedRssis[WIFI_SCAN_CACHE_LIMIT];
size_t cachedNetworkCount = 0;
unsigned long cachedNetworkScanMs = 0;
bool wifiScanInProgress = false;

bool lastTriggerState = HIGH;
unsigned long lastTriggerMs = 0;
volatile bool buttonTriggerPending = false;
volatile unsigned long buttonTriggerAtMs = 0;
uint8_t *triggerJpeg = NULL;
size_t triggerJpegLen = 0;
unsigned long triggerCaptureSeq = 0;

String logBuffer[LOG_BUFFER_LINES];
size_t logWriteIndex = 0;
size_t logStoredCount = 0;

struct DeviceConfig {
  String ssid;
  String password;
};

DeviceConfig deviceConfig;

#define PWDN_GPIO_NUM     32
#define RESET_GPIO_NUM    -1
#define XCLK_GPIO_NUM      0
#define SIOD_GPIO_NUM     26
#define SIOC_GPIO_NUM     27

#define Y9_GPIO_NUM       35
#define Y8_GPIO_NUM       34
#define Y7_GPIO_NUM       39
#define Y6_GPIO_NUM       36
#define Y5_GPIO_NUM       21
#define Y4_GPIO_NUM       19
#define Y3_GPIO_NUM       18
#define Y2_GPIO_NUM        5
#define VSYNC_GPIO_NUM    25
#define HREF_GPIO_NUM     23
#define PCLK_GPIO_NUM     22

static String buildStatusHtml();

static String htmlEscape(const String &value) {
  String out = value;
  out.replace("&", "&amp;");
  out.replace("<", "&lt;");
  out.replace(">", "&gt;");
  out.replace("\"", "&quot;");
  out.replace("'", "&#39;");
  return out;
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

static String base64Encode(const uint8_t *data, size_t len) {
  static const char table[] = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
  String out;
  out.reserve(((len + 2) / 3) * 4);

  for (size_t i = 0; i < len; i += 3) {
    uint32_t octetA = data[i];
    uint32_t octetB = (i + 1 < len) ? data[i + 1] : 0;
    uint32_t octetC = (i + 2 < len) ? data[i + 2] : 0;
    uint32_t triple = (octetA << 16) | (octetB << 8) | octetC;

    out += table[(triple >> 18) & 0x3F];
    out += table[(triple >> 12) & 0x3F];
    out += (i + 1 < len) ? table[(triple >> 6) & 0x3F] : '=';
    out += (i + 2 < len) ? table[triple & 0x3F] : '=';
  }

  return out;
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

static String jsonExtractNumberString(const String &json, const char *key, const String &fallback = "") {
  String needle = "\"" + String(key) + "\"";
  int pos = json.indexOf(needle);
  if (pos < 0) return fallback;
  int colon = json.indexOf(':', pos + needle.length());
  if (colon < 0) return fallback;
  int start = colon + 1;
  while (start < (int)json.length() && (json.charAt(start) == ' ' || json.charAt(start) == '\t')) start++;
  int end = start;
  while (end < (int)json.length()) {
    char c = json.charAt(end);
    if ((c >= '0' && c <= '9') || c == '.' || c == '-') {
      end++;
      continue;
    }
    break;
  }
  if (end <= start) return fallback;
  return json.substring(start, end);
}

static String urlEncode(const String &value) {
  const char *hex = "0123456789ABCDEF";
  String out;
  out.reserve(value.length() * 3);
  for (size_t i = 0; i < value.length(); i++) {
    unsigned char c = (unsigned char) value.charAt(i);
    if ((c >= 'a' && c <= 'z') ||
        (c >= 'A' && c <= 'Z') ||
        (c >= '0' && c <= '9') ||
        c == '-' || c == '_' || c == '.' || c == '~') {
      out += (char) c;
    } else if (c == ' ') {
      out += "%20";
    } else {
      out += '%';
      out += hex[(c >> 4) & 0x0F];
      out += hex[c & 0x0F];
    }
  }
  return out;
}

static String ipToString(const IPAddress &ip) {
  if (ip == IPAddress((uint32_t)0)) {
    return "";
  }
  return ip.toString();
}

static IPAddress parseIpOrZero(const String &raw) {
  IPAddress ip;
  if (ip.fromString(raw)) {
    return ip;
  }
  return IPAddress((uint32_t)0);
}

static void writeTelnet(const String &line) {
  for (size_t i = 0; i < TELNET_CLIENT_SLOTS; i++) {
    if (telnetClients[i] && telnetClients[i].connected()) {
      telnetClients[i].println(line);
    }
  }
}

static void pushLogLine(const String &line) {
  Serial.println(line);
  writeTelnet(line);
  logBuffer[logWriteIndex] = line;
  logWriteIndex = (logWriteIndex + 1) % LOG_BUFFER_LINES;
  if (logStoredCount < LOG_BUFFER_LINES) {
    logStoredCount++;
  }
}

static void logf(const char *fmt, ...) {
  char buffer[256];
  va_list args;
  va_start(args, fmt);
  vsnprintf(buffer, sizeof(buffer), fmt, args);
  va_end(args);
  pushLogLine(String(buffer));
}

static void loadConfig() {
  prefs.begin(PREF_NAMESPACE, true);
  deviceConfig.ssid = prefs.getString("ssid", "");
  deviceConfig.password = prefs.getString("pass", "");
  prefs.end();
  deviceHostname = "fortiroom-cam";
  savedServerBase = "";
}

static void saveConfig(const DeviceConfig &config) {
  prefs.begin(PREF_NAMESPACE, false);
  prefs.putString("ssid", config.ssid);
  prefs.putString("pass", config.password);
  prefs.remove("host");
  prefs.remove("server");
  prefs.remove("useStatic");
  prefs.remove("ip");
  prefs.remove("gateway");
  prefs.remove("subnet");
  prefs.remove("dns");
  prefs.end();
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
  String configured = normalizedServerBaseUrl(savedServerBase);
  if (configured.length() > 0) {
    return configured;
  }
  String fallback = normalizedServerBaseUrl(String(DEFAULT_SERVER_BASE_URL));
  return fallback;
}

static String currentBaseUrl() {
  String host = portalMode ? WiFi.softAPIP().toString() : WiFi.localIP().toString();
  return "http://" + host;
}

static void clearSavedWiFiConfig() {
  prefs.begin(PREF_NAMESPACE, false);
  prefs.remove("ssid");
  prefs.remove("pass");
  prefs.remove("host");
  prefs.remove("server");
  prefs.remove("useStatic");
  prefs.remove("ip");
  prefs.remove("gateway");
  prefs.remove("subnet");
  prefs.remove("dns");
  prefs.end();
}

static void addCorsHeaders() {
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.sendHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  server.sendHeader("Access-Control-Allow-Headers", "Content-Type");
}

static void initFlashLed() {
#if ESP_ARDUINO_VERSION_MAJOR >= 3
  ledcAttach(FLASH_LED_PIN, FLASH_LED_PWM_FREQ, FLASH_LED_PWM_RESOLUTION);
#else
  ledcSetup(FLASH_LED_PWM_CHANNEL, FLASH_LED_PWM_FREQ, FLASH_LED_PWM_RESOLUTION);
  ledcAttachPin(FLASH_LED_PIN, FLASH_LED_PWM_CHANNEL);
#endif
}

static void setFlashLed(bool on) {
#if ESP_ARDUINO_VERSION_MAJOR >= 3
  ledcWrite(FLASH_LED_PIN, on ? FLASH_LED_BRIGHTNESS : 0);
#else
  ledcWrite(FLASH_LED_PWM_CHANNEL, on ? FLASH_LED_BRIGHTNESS : 0);
#endif
}

static void prepareFlashForCapture(uint8_t settleFrames = 3, uint16_t settleDelayMs = 80) {
  setFlashLed(true);
  delay(260);

  for (uint8_t i = 0; i < settleFrames; i++) {
    camera_fb_t *tmp = esp_camera_fb_get();
    if (tmp) {
      esp_camera_fb_return(tmp);
    }
    delay(settleDelayMs);
  }
}

static void finishFlashCapture() {
  setFlashLed(false);
}

static void blinkFlashStatus(uint8_t times, unsigned long onMs, unsigned long offMs) {
  for (uint8_t i = 0; i < times; i++) {
    setFlashLed(true);
    delay(onMs);
    setFlashLed(false);
    if (i + 1 < times) {
      delay(offMs);
    }
  }
}

static void blinkPortalWaitingPattern() {
  blinkFlashStatus(2, 80, 120);
}

static void blinkWifiConnectedPattern() {
  blinkFlashStatus(2, 40, 120);
}

static void completeCachedNetworksFromScan(int networkCount) {
  cachedNetworkCount = 0;
  if (networkCount <= 0) {
    WiFi.scanDelete();
    cachedNetworkScanMs = millis();
    wifiScanInProgress = false;
    logf("Wi-Fi scan complete. No networks found.");
    return;
  }

  for (int i = 0; i < networkCount && cachedNetworkCount < WIFI_SCAN_CACHE_LIMIT; i++) {
    String ssid = WiFi.SSID(i);
    if (ssid.length() == 0) {
      continue;
    }

    bool duplicate = false;
    for (size_t j = 0; j < cachedNetworkCount; j++) {
      if (cachedSsids[j] == ssid) {
        duplicate = true;
        if (WiFi.RSSI(i) > cachedRssis[j]) {
          cachedRssis[j] = WiFi.RSSI(i);
        }
        break;
      }
    }

    if (!duplicate) {
      cachedSsids[cachedNetworkCount] = ssid;
      cachedRssis[cachedNetworkCount] = WiFi.RSSI(i);
      cachedNetworkCount++;
    }
  }

  for (size_t i = 0; i < cachedNetworkCount; i++) {
    for (size_t j = i + 1; j < cachedNetworkCount; j++) {
      if (cachedRssis[j] > cachedRssis[i]) {
        String ssidTmp = cachedSsids[i];
        int32_t rssiTmp = cachedRssis[i];
        cachedSsids[i] = cachedSsids[j];
        cachedRssis[i] = cachedRssis[j];
        cachedSsids[j] = ssidTmp;
        cachedRssis[j] = rssiTmp;
      }
    }
  }

  WiFi.scanDelete();
  cachedNetworkScanMs = millis();
  wifiScanInProgress = false;
  logf("Wi-Fi scan complete. %u networks cached.", (unsigned int) cachedNetworkCount);
}

static void startAsyncNetworkScan() {
  if (wifiScanInProgress) {
    return;
  }
  WiFi.scanDelete();
  wifiScanInProgress = true;
  cachedNetworkCount = 0;
  WiFi.scanNetworks(true);
  logf("Wi-Fi scan started...");
}

static void pollAsyncNetworkScan() {
  if (!wifiScanInProgress) {
    return;
  }
  int networkCount = WiFi.scanComplete();
  if (networkCount == WIFI_SCAN_RUNNING) {
    return;
  }
  if (networkCount == WIFI_SCAN_FAILED) {
    wifiScanInProgress = false;
    logf("Wi-Fi scan failed.");
    return;
  }
  completeCachedNetworksFromScan(networkCount);
}

static void resetCameraPower() {
  pinMode(PWDN_GPIO_NUM, OUTPUT);
  digitalWrite(PWDN_GPIO_NUM, HIGH);
  delay(150);
  digitalWrite(PWDN_GPIO_NUM, LOW);
  delay(150);
}

static void flushCameraFrames(uint8_t count = 3, uint16_t delayMs = 80) {
  for (uint8_t i = 0; i < count; i++) {
    camera_fb_t *oldFrame = esp_camera_fb_get();
    if (oldFrame) {
      esp_camera_fb_return(oldFrame);
    }
    delay(delayMs);
  }
}

static void setCameraForPreview() {
  sensor_t *s = esp_camera_sensor_get();
  if (!s) return;

  if (psramFound()) {
    s->set_framesize(s, FRAMESIZE_VGA);
    s->set_quality(s, 5);
  } else {
    s->set_framesize(s, FRAMESIZE_QVGA);
    s->set_quality(s, 9);
  }
}

static void setCameraForStillCapture() {
  sensor_t *s = esp_camera_sensor_get();
  if (!s) return;
  bool isOv3660 = (s->id.PID == OV3660_PID);

  if (psramFound()) {
    s->set_framesize(s, FRAMESIZE_VGA);
    s->set_quality(s, 4);
  } else {
    s->set_framesize(s, FRAMESIZE_VGA);
    s->set_quality(s, 7);
  }

  // Use a brighter, cleaner still-photo profile than the live preview.
  // OV3660 often benefits from stronger exposure guidance indoors.
  s->set_brightness(s, 4);
  s->set_contrast(s, 1);
  s->set_saturation(s, 2);
  s->set_gainceiling(s, (gainceiling_t)8);
  s->set_gain_ctrl(s, 1);
  s->set_agc_gain(s, isOv3660 ? 14 : 12);
  s->set_exposure_ctrl(s, 1);
  s->set_aec2(s, 1);
  s->set_aec_value(s, isOv3660 ? 980 : 720);
  s->set_ae_level(s, isOv3660 ? 5 : 4);
  s->set_dcw(s, 1);
  s->set_denoise(s, 7);
  s->set_sharpness(s, 2);
}

static bool storeTriggeredCapture() {
  setCameraForStillCapture();
  prepareFlashForCapture(4, 90);

  camera_fb_t *fb = esp_camera_fb_get();
  if (!fb) {
    finishFlashCapture();
    setCameraForPreview();
    return false;
  }

  uint8_t *copy = psramFound() ? (uint8_t *) ps_malloc(fb->len) : (uint8_t *) malloc(fb->len);
  if (!copy) {
    esp_camera_fb_return(fb);
    finishFlashCapture();
    setCameraForPreview();
    return false;
  }

  memcpy(copy, fb->buf, fb->len);
  size_t copyLen = fb->len;
  esp_camera_fb_return(fb);

  if (triggerJpeg) {
    free(triggerJpeg);
  }

  triggerJpeg = copy;
  triggerJpegLen = copyLen;
  triggerCaptureSeq++;

  finishFlashCapture();
  setCameraForPreview();
  flushCameraFrames(2, 60);

  return true;
}

static bool initCamera() {
  resetCameraPower();

  camera_config_t config;
  memset(&config, 0, sizeof(config));
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sccb_sda = SIOD_GPIO_NUM;
  config.pin_sccb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;

  if (psramFound()) {
    config.frame_size = FRAMESIZE_VGA;
    config.jpeg_quality = 5;
    config.fb_count = 1;
  } else {
    config.frame_size = FRAMESIZE_QVGA;
    config.jpeg_quality = 9;
    config.fb_count = 1;
  }

  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    logf("Camera init failed with error 0x%x", err);
    return false;
  }

  sensor_t *s = esp_camera_sensor_get();
  if (s) {
    s->set_framesize(s, psramFound() ? FRAMESIZE_VGA : FRAMESIZE_QVGA);
    s->set_quality(s, psramFound() ? 5 : 9);

    // Indoor portrait tuning: keep the image brighter without crushing shadows
    // or letting auto-gain introduce too much visible grain.
    s->set_brightness(s, 3);
    s->set_contrast(s, 0);
    s->set_saturation(s, 0);

    s->set_gainceiling(s, (gainceiling_t)6);
    s->set_gain_ctrl(s, 1);
    s->set_agc_gain(s, 10);

    s->set_whitebal(s, 1);
    s->set_awb_gain(s, 1);
    s->set_wb_mode(s, 0);

    s->set_exposure_ctrl(s, 1);
    s->set_aec2(s, 1);
    s->set_ae_level(s, 3);

    s->set_raw_gma(s, 1);
    s->set_lenc(s, 1);
    s->set_hmirror(s, 1);
    s->set_vflip(s, 1);
    s->set_bpc(s, 1);
    s->set_wpc(s, 1);
    s->set_colorbar(s, 0);

    logf("Camera tuning applied: preview=%s still=%s brightness=3 contrast=0 ae_level=3 agc_gain=10 gainceiling=6 flash=%d",
         psramFound() ? "VGA" : "QVGA",
         psramFound() ? "VGA" : "VGA",
         FLASH_LED_BRIGHTNESS);
  }

  flushCameraFrames(4, 80);

  logf("Camera initialized. Profile: %s",
       psramFound() ? "VGA preview / VGA still capture with flash" : "QVGA preview / VGA still capture with flash");
  return true;
}

static void handleOptions() {
  addCorsHeaders();
  server.send(204);
}

static void sendRedirectRoot() {
  addCorsHeaders();
  String host = portalMode ? WiFi.softAPIP().toString() : WiFi.localIP().toString();
  server.sendHeader("Location", "http://" + host + "/", true);
  server.send(302, "text/plain", "Redirecting...");
}

static void sendPortalPageDirect() {
  addCorsHeaders();
  server.sendHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
  server.sendHeader("Pragma", "no-cache");
  server.sendHeader("Expires", "0");
  server.send(200, "text/html", buildStatusHtml());
}

static String buildStatusHtml() {
  String html;
  html += "<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>";
  html += "<title>FORTIROOM ESP32-CAM</title>";
  html += "<style>";
  html += "body{font-family:Arial,sans-serif;background:#10161d;color:#eef2f7;margin:0;padding:24px}";
  html += "h1{margin-top:0}a{color:#7fd4ff}";
  html += "section{background:#18212b;border-radius:16px;padding:18px;margin-bottom:16px}";
  html += "input,button{width:100%;box-sizing:border-box;padding:12px;margin:6px 0 12px;border-radius:10px;border:1px solid #425468}";
  html += "input{background:#0f1720;color:#fff}";
  html += "button{background:#17a34a;color:#fff;font-weight:bold;border:0;cursor:pointer}";
  html += ".danger{background:#c0392b}.mono{font-family:Consolas,monospace}";
  html += ".grid{display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(280px,1fr))}";
  html += "</style>";
  html += "</head><body><h1>FORTIROOM ESP32-CAM</h1>";

  html += "<section><strong>Mode:</strong> ";
  html += portalMode ? "AP Configuration Portal" : "Wi-Fi Connected";
  html += "<br><strong>IP:</strong> <span class='mono'>" + htmlEscape(portalMode ? WiFi.softAPIP().toString() : WiFi.localIP().toString()) + "</span>";
  if (!portalMode) {
    html += "<br><strong>SSID:</strong> " + htmlEscape(WiFi.SSID());
    html += "<br><strong>RSSI:</strong> " + String(WiFi.RSSI());
  }
  html += "</section>";

  if (portalMode) {
    html += "<section><h2>Connect to Wi-Fi</h2>";
    html += "<form method='post' action='/save-config'>";
    html += "<label>SSID</label><input name='ssid' value='" + htmlEscape(deviceConfig.ssid) + "' required>";
    html += "<label>Password</label><input name='password' type='password'>";
    html += "<button type='submit'>Save and Connect</button>";
    html += "</form></section>";
  } else {
    html += "<div class='grid'>";
    html += "<section><h2>Camera</h2>";
    html += "<div><a href='/capture' target='_blank'>Open /capture</a></div>";
    html += "<div><a href='/trigger-capture' target='_blank'>Open /trigger-capture</a></div>";
    html += "<div><a href='/button' target='_blank'>Open /button</a></div>";
    html += "<div><a href='/health' target='_blank'>Open /health</a></div>";
    html += "</section>";
    html += "<section><h2>Wireless Development</h2>";
    html += "<div><a href='/update'>Web OTA Update</a></div>";
    html += "<div><a href='/logs'>Live Logs</a></div>";
    html += "<div>Telnet: <code>" + htmlEscape(WiFi.localIP().toString()) + ":" + String(TELNET_PORT) + "</code></div>";
    html += "<div>ArduinoOTA Host: <code>" + htmlEscape(deviceHostname) + ".local</code></div>";
    html += "</section>";
    html += "<section><h2>Configuration</h2><form method='post' action='/save-config'>";
    html += "<label>SSID</label><input name='ssid' required value='" + htmlEscape(deviceConfig.ssid) + "'>";
    html += "<label>Password</label><input name='password' type='password' value='" + htmlEscape(deviceConfig.password) + "'>";
    html += "<button type='submit'>Save and Reboot</button></form></section>";
    html += "</div>";
  }

  html += "<section><h2>Actions</h2>";
  html += "<form method='post' action='/reset-wifi'><button class='danger' type='submit'>Reset Saved Wi-Fi</button></form>";
  html += "<p><a href='/health'>/health</a></p>";
  html += "</section>";
  html += "</body></html>";
  return html;
}

static String buildCountdownHtml(const String &title, const String &message, int seconds) {
  String html;
  html += "<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>";
  html += "<title>" + htmlEscape(title) + "</title>";
  html += "<style>body{font-family:Arial,sans-serif;background:#111;color:#f4f4f4;margin:0;padding:24px;display:flex;align-items:center;justify-content:center;min-height:100vh}section{background:#1b1b1b;border-radius:16px;padding:24px;max-width:520px;width:100%;text-align:center}h1{margin-top:0}.count{font-size:48px;font-weight:bold;color:#18a058;margin:16px 0}</style>";
  html += "</head><body><section>";
  html += "<h1>" + htmlEscape(title) + "</h1>";
  html += "<p>" + htmlEscape(message) + "</p>";
  html += "<div class='count' id='count'>" + String(seconds) + "</div>";
  html += "<script>let s=" + String(seconds) + ";const el=document.getElementById('count');const timer=setInterval(()=>{s--;if(s<=0){el.textContent='0';clearInterval(timer);}else{el.textContent=String(s);}},1000);</script>";
  html += "</section></body></html>";
  return html;
}

static void handleRoot() {
  addCorsHeaders();
  server.sendHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
  server.sendHeader("Pragma", "no-cache");
  server.sendHeader("Expires", "0");
  server.send(200, "text/html", buildStatusHtml());
}

static void handleCaptivePortalProbe() {
  if (portalMode) {
    sendPortalPageDirect();
    return;
  }
  handleRoot();
}

static void handleHealth() {
  addCorsHeaders();
  String json = "{";
  json += "\"status\":\"ok\",";
  json += "\"mode\":\"" + String(portalMode ? "ap" : "sta") + "\",";
  json += "\"hostname\":\"" + jsonEscape(deviceHostname) + "\",";
  json += "\"ip\":\"" + String(portalMode ? WiFi.softAPIP().toString() : WiFi.localIP().toString()) + "\",";
  json += "\"rssi\":" + String(wifiReady ? WiFi.RSSI() : 0) + ",";
  json += "\"psram\":" + String(psramFound() ? "true" : "false") + ",";
  json += "\"capture_seq\":" + String(triggerCaptureSeq);
  json += "}";
  server.send(200, "application/json", json);
}

static void handleLogsJson() {
  addCorsHeaders();
  String json = "{\"logs\":[";
  for (size_t i = 0; i < logStoredCount; i++) {
    size_t index = (logWriteIndex + LOG_BUFFER_LINES - logStoredCount + i) % LOG_BUFFER_LINES;
    if (i > 0) {
      json += ",";
    }
    json += "\"" + jsonEscape(logBuffer[index]) + "\"";
  }
  json += "]}";
  server.send(200, "application/json", json);
}

static void handleNetworksJson() {
  addCorsHeaders();
  String json = "{";
  json += "\"scanning\":" + String(wifiScanInProgress ? "true" : "false") + ",";
  json += "\"last_scan_seconds\":";
  if (cachedNetworkScanMs == 0) {
    json += "-1,";
  } else {
    json += String((millis() - cachedNetworkScanMs) / 1000UL) + ",";
  }
  json += "\"networks\":[";
  for (size_t i = 0; i < cachedNetworkCount; i++) {
    if (i > 0) {
      json += ",";
    }
    json += "{\"ssid\":\"" + jsonEscape(cachedSsids[i]) + "\",\"rssi\":" + String(cachedRssis[i]) + "}";
  }
  json += "]}";
  server.send(200, "application/json", json);
}

static void handleLogsPage() {
  addCorsHeaders();
  String html;
  html += "<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>";
  html += "<title>FORTIROOM Logs</title>";
  html += "<style>body{font-family:Consolas,monospace;background:#0c1016;color:#d8f0ff;margin:0;padding:24px}pre{background:#111827;border-radius:12px;padding:16px;white-space:pre-wrap;word-break:break-word;min-height:300px}a{color:#7fd4ff}</style>";
  html += "</head><body><h1>Live Logs</h1><p><a href='/'>Back</a></p><pre id='log'>Loading...</pre>";
  html += "<script>async function refresh(){const r=await fetch('/logs.json',{cache:'no-store'});const j=await r.json();document.getElementById('log').textContent=(j.logs||[]).join('\\n');}refresh();setInterval(refresh,2000);</script>";
  html += "</body></html>";
  server.send(200, "text/html", html);
}

static void handleButtonTrigger() {
  addCorsHeaders();
  bool pressed = buttonTriggerPending;
  unsigned long pressedAt = buttonTriggerAtMs;
  buttonTriggerPending = false;
  String host = portalMode ? WiFi.softAPIP().toString() : WiFi.localIP().toString();
  String json = "{";
  json += "\"pressed\":" + String(pressed ? "true" : "false") + ",";
  json += "\"pressed_at_ms\":" + String(pressedAt) + ",";
  json += "\"capture_seq\":" + String(triggerCaptureSeq) + ",";
  json += "\"ip\":\"" + host + "\",";
  json += "\"capture_url\":\"http://" + host + "/trigger-capture?seq=" + String(triggerCaptureSeq) + "\"";
  json += "}";
  server.send(200, "application/json", json);
}

static void handleVerifyBooking() {
  addCorsHeaders();

  if (portalMode || !wifiReady || WiFi.status() != WL_CONNECTED) {
    server.send(503, "application/json", "{\"success\":false,\"error\":\"Camera is not connected to Wi-Fi\"}");
    return;
  }

  String username = server.arg("username");
  username.trim();
  if (username.length() == 0) {
    String body = server.arg("plain");
    if (body.length() > 0) {
      username = jsonExtractString(body, "username", "");
      username.trim();
    }
  }

  if (username.length() == 0) {
    server.send(400, "application/json", "{\"success\":false,\"error\":\"Username is required\"}");
    return;
  }

  setCameraForStillCapture();
  prepareFlashForCapture(4, 90);
  camera_fb_t *fb = esp_camera_fb_get();
  finishFlashCapture();
  setCameraForPreview();
  flushCameraFrames(2, 60);

  if (!fb) {
    server.send(500, "application/json", "{\"success\":false,\"error\":\"Camera capture failed during verification\"}");
    return;
  }

  String captureDataUrl = "data:image/jpeg;base64," + base64Encode(fb->buf, fb->len);
  esp_camera_fb_return(fb);

  String serverBaseUrl = getServerBaseUrl();
  if (serverBaseUrl.length() == 0) {
    server.send(500, "application/json", "{\"success\":false,\"error\":\"Server base URL is not configured\"}");
    return;
  }

  String verifyEndpoint = serverBaseUrl + FACE_VERIFY_PATH;
  String payload = "{";
  payload += "\"username\":\"" + jsonEscape(username) + "\",";
  payload += "\"esp32_capture_url\":\"" + jsonEscape(currentBaseUrl() + "/capture") + "\",";
  payload += "\"capture_image_data\":\"" + jsonEscape(captureDataUrl) + "\"";
  payload += "}";

  WiFiClient client;
  HTTPClient http;
  http.setTimeout(VERIFY_TIMEOUT_MS);
  if (!http.begin(client, verifyEndpoint)) {
    server.send(500, "application/json", "{\"success\":false,\"error\":\"Failed to start verification HTTP client\"}");
    return;
  }

  http.addHeader("Content-Type", "application/json");
  int statusCode = http.POST(payload);
  String responseBody = http.getString();
  http.end();

  bool verified = jsonExtractBool(responseBody, "verified", false);
  String similarity = jsonExtractNumberString(responseBody, "similarity", "null");
  String threshold = jsonExtractNumberString(responseBody, "threshold", "null");
  String verifyAttempt = jsonExtractString(responseBody, "verify_attempt", "");

  logf(
    "Booking verification for '%s' -> %d | verified=%s similarity=%s threshold=%s attempt=%s",
    username.c_str(),
    statusCode,
    verified ? "true" : "false",
    similarity.c_str(),
    threshold.c_str(),
    verifyAttempt.length() > 0 ? verifyAttempt.c_str() : "-"
  );

  if (statusCode < 200 || statusCode >= 300) {
    String errorJson = "{";
    errorJson += "\"success\":false,";
    errorJson += "\"error\":\"Verification request failed\",";
    errorJson += "\"status_code\":" + String(statusCode) + ",";
    errorJson += "\"raw_response\":\"" + jsonEscape(responseBody) + "\"";
    errorJson += "}";
    server.send(statusCode > 0 ? statusCode : 502, "application/json", errorJson);
    return;
  }
  String compactJson = "{";
  compactJson += "\"success\":true,";
  compactJson += "\"verified\":" + String(verified ? "true" : "false") + ",";
  compactJson += "\"similarity\":";
  compactJson += (similarity.length() > 0 ? similarity : "null");
  compactJson += ",";
  compactJson += "\"threshold\":";
  compactJson += (threshold.length() > 0 ? threshold : "null");
  compactJson += ",";
  compactJson += "\"verify_attempt\":\"" + jsonEscape(verifyAttempt) + "\",";
  compactJson += "\"username\":\"" + jsonEscape(username) + "\"";
  compactJson += "}";

  server.send(200, "application/json", compactJson);
}

static void sendJpegBuffer(uint8_t *buffer, size_t len) {
  WiFiClient client = server.client();
  addCorsHeaders();
  server.sendHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
  server.sendHeader("Pragma", "no-cache");
  server.sendHeader("Expires", "0");
  server.setContentLength(len);
  server.send(200, "image/jpeg", "");
  const size_t chunkSize = 1024;
  size_t sent = 0;
  while (sent < len) {
    size_t remaining = len - sent;
    size_t toWrite = remaining < chunkSize ? remaining : chunkSize;
    size_t written = client.write(buffer + sent, toWrite);
    if (written == 0) {
      delay(1);
      continue;
    }
    sent += written;
  }
}

static void handleCapture() {
  setCameraForStillCapture();
  prepareFlashForCapture(4, 90);

  camera_fb_t *fb = esp_camera_fb_get();
  if (!fb) {
    finishFlashCapture();
    setCameraForPreview();
    addCorsHeaders();
    server.send(500, "application/json", "{\"error\":\"Camera capture failed\"}");
    return;
  }

  sendJpegBuffer(fb->buf, fb->len);
  esp_camera_fb_return(fb);

  finishFlashCapture();
  setCameraForPreview();
  flushCameraFrames(2, 60);
}

static void handleTriggerCapture() {
  if (!triggerJpeg || triggerJpegLen == 0) {
    addCorsHeaders();
    server.send(404, "application/json", "{\"error\":\"No physical-trigger capture is available yet\"}");
    return;
  }
  sendJpegBuffer(triggerJpeg, triggerJpegLen);
}

static void handleUpdatePage() {
  addCorsHeaders();
  String html;
  html += "<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>";
  html += "<title>FORTIROOM OTA Update</title>";
  html += "<style>body{font-family:Arial,sans-serif;background:#111;color:#f4f4f4;margin:0;padding:24px}section{background:#1b1b1b;border-radius:16px;padding:18px}input,button{padding:10px;border-radius:8px}button{border:0;background:#18a058;color:#fff;font-weight:bold}a{color:#7fd4ff}</style>";
  html += "</head><body><section><h1>Web OTA Update</h1><p><a href='/'>Back</a></p>";
  html += "<form method='POST' action='/update' enctype='multipart/form-data'>";
  html += "<input type='file' name='firmware' accept='.bin' required><br><br>";
  html += "<button type='submit'>Upload Firmware</button>";
  html += "</form></section></body></html>";
  server.send(200, "text/html", html);
}

static void handleUpdateFinished() {
  addCorsHeaders();
  bool success = !Update.hasError();
  server.send(200, "text/plain", success ? "Update successful. Rebooting..." : "Update failed.");
  delay(500);
  if (success) {
    logf("Web OTA update completed. Rebooting...");
    ESP.restart();
  } else {
    logf("Web OTA update failed.");
  }
}

static void handleUpdateUpload() {
  HTTPUpload& upload = server.upload();
  if (upload.status == UPLOAD_FILE_START) {
    logf("Web OTA upload started: %s", upload.filename.c_str());
    if (!Update.begin(UPDATE_SIZE_UNKNOWN)) {
      Update.printError(Serial);
    }
  } else if (upload.status == UPLOAD_FILE_WRITE) {
    if (Update.write(upload.buf, upload.currentSize) != upload.currentSize) {
      Update.printError(Serial);
    }
  } else if (upload.status == UPLOAD_FILE_END) {
    if (Update.end(true)) {
      logf("Web OTA upload finished successfully: %u bytes", upload.totalSize);
    } else {
      Update.printError(Serial);
    }
  }
}

static void handleSaveConfig() {
  DeviceConfig updated;
  updated.ssid = server.arg("ssid");
  updated.password = server.arg("password");

  saveConfig(updated);
  addCorsHeaders();
  server.send(200, "text/html", buildCountdownHtml("Configuration saved", "Device will reboot in:", 2));
  logf("Configuration updated for SSID '%s'. Rebooting...", updated.ssid.c_str());
  delay(2000);
  ESP.restart();
}

static void handleResetWiFi() {
  clearSavedWiFiConfig();
  addCorsHeaders();
  server.send(200, "text/html", buildCountdownHtml("Saved Wi-Fi cleared", "Device will reboot into setup mode in:", 2));
  logf("Saved Wi-Fi configuration cleared. Rebooting to setup portal...");
  delay(2000);
  ESP.restart();
}

static void handleReboot() {
  addCorsHeaders();
  server.send(200, "text/html", buildCountdownHtml("Rebooting", "Device will reboot in:", 1));
  logf("Reboot requested from web interface.");
  delay(1000);
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
  server.on("/capture", HTTP_GET, handleCapture);
  server.on("/button", HTTP_GET, handleButtonTrigger);
  server.on("/trigger-capture", HTTP_GET, handleTriggerCapture);
  server.on("/verify-booking", HTTP_GET, handleVerifyBooking);
  server.on("/verify-booking", HTTP_POST, handleVerifyBooking);
  server.on("/health", HTTP_GET, handleHealth);
  server.on("/logs", HTTP_GET, handleLogsPage);
  server.on("/logs.json", HTTP_GET, handleLogsJson);
  server.on("/save-config", HTTP_POST, handleSaveConfig);
  server.on("/reset-wifi", HTTP_POST, handleResetWiFi);
  server.on("/reboot", HTTP_GET, handleReboot);
  server.on("/update", HTTP_GET, handleUpdatePage);
  server.on("/update", HTTP_POST, handleUpdateFinished, handleUpdateUpload);

  server.on("/generate_204", HTTP_OPTIONS, handleOptions);
  server.on("/gen_204", HTTP_OPTIONS, handleOptions);
  server.on("/hotspot-detect.html", HTTP_OPTIONS, handleOptions);
  server.on("/library/test/success.html", HTTP_OPTIONS, handleOptions);
  server.on("/success.txt", HTTP_OPTIONS, handleOptions);
  server.on("/canonical.html", HTTP_OPTIONS, handleOptions);
  server.on("/connecttest.txt", HTTP_OPTIONS, handleOptions);
  server.on("/ncsi.txt", HTTP_OPTIONS, handleOptions);
  server.on("/fwlink", HTTP_OPTIONS, handleOptions);
  server.on("/redirect", HTTP_OPTIONS, handleOptions);
  server.on("/capture", HTTP_OPTIONS, handleOptions);
  server.on("/button", HTTP_OPTIONS, handleOptions);
  server.on("/trigger-capture", HTTP_OPTIONS, handleOptions);
  server.on("/verify-booking", HTTP_OPTIONS, handleOptions);
  server.on("/health", HTTP_OPTIONS, handleOptions);
  server.on("/logs.json", HTTP_OPTIONS, handleOptions);
  server.on("/save-config", HTTP_OPTIONS, handleOptions);
  server.on("/reset-wifi", HTTP_OPTIONS, handleOptions);
  server.on("/update", HTTP_OPTIONS, handleOptions);

  server.onNotFound([]() {
    if (portalMode) {
      sendRedirectRoot();
      return;
    }
    addCorsHeaders();
    server.send(404, "application/json", "{\"error\":\"Not found\"}");
  });
}

static bool applyStaticIpIfNeeded() {
  return true;
}

static bool connectToSavedWiFi() {
  if (USE_HARDCODED_WIFI) {
    deviceConfig.ssid = HARDCODED_WIFI_SSID;
    deviceConfig.password = HARDCODED_WIFI_PASS;
  }

  if (deviceConfig.ssid.length() == 0) {
    logf("No saved Wi-Fi credentials found.");
    return false;
  }

  WiFi.mode(WIFI_STA);
  WiFi.disconnect(true, true);
  delay(200);
  applyStaticIpIfNeeded();
  logf("Connecting to Wi-Fi SSID '%s'...", deviceConfig.ssid.c_str());
  WiFi.begin(deviceConfig.ssid.c_str(), deviceConfig.password.c_str());

  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && (millis() - start) < WIFI_CONNECT_TIMEOUT_MS) {
    delay(500);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() != WL_CONNECTED) {
    logf("Wi-Fi connection failed.");
    return false;
  }

  wifiReady = true;
  portalMode = false;
  logf("Wi-Fi connected. IP: %s", WiFi.localIP().toString().c_str());
  blinkWifiConnectedPattern();
  return true;
}

static void startPortalMode() {
  portalMode = true;
  wifiReady = false;
  WiFi.mode(WIFI_AP_STA);
  WiFi.softAP(AP_SSID, AP_PASS);
  dnsServer.start(DNS_PORT, "*", WiFi.softAPIP());
  lastPortalBlinkMs = 0;
  logf("Config portal started. AP SSID: %s", AP_SSID);
  logf("Portal IP: %s", WiFi.softAPIP().toString().c_str());
}

static void reportCameraRegistration(bool force) {
  if (!wifiReady || portalMode || WiFi.status() != WL_CONNECTED) {
    return;
  }

  unsigned long now = millis();
  unsigned long minInterval = lastCameraRegistryReportOk
    ? CAMERA_REGISTRY_REPORT_INTERVAL_MS
    : CAMERA_REGISTRY_RETRY_INTERVAL_MS;

  if (!force && lastCameraRegistryReportMs != 0 && (now - lastCameraRegistryReportMs) < minInterval) {
    return;
  }

  String serverBaseUrl = getServerBaseUrl();
  if (serverBaseUrl.length() == 0) {
    lastCameraRegistryReportMs = now;
    lastCameraRegistryReportOk = false;
    if (!lastCameraRegistryReportOk) {
      logf("Camera registry skipped: browser-assisted registration mode (no PHP server base URL configured).");
    }
    return;
  }

  String localIp = WiFi.localIP().toString();
  String localBaseUrl = "http://" + localIp;
  String endpointUrl = serverBaseUrl + CAMERA_REGISTRY_PATH;
  String payload = "{";
  payload += "\"device_id\":\"" + jsonEscape(String(CAMERA_DEVICE_ID)) + "\",";
  payload += "\"device\":\"fortiroom-cam\",";
  payload += "\"hostname\":\"" + jsonEscape(deviceHostname) + "\",";
  payload += "\"ssid\":\"" + jsonEscape(WiFi.SSID()) + "\",";
  payload += "\"ip\":\"" + jsonEscape(localIp) + "\",";
  payload += "\"base_url\":\"" + jsonEscape(localBaseUrl) + "\",";
  payload += "\"capture_url\":\"" + jsonEscape(localBaseUrl + "/capture") + "\",";
  payload += "\"health_url\":\"" + jsonEscape(localBaseUrl + "/health") + "\",";
  payload += "\"button_url\":\"" + jsonEscape(localBaseUrl + "/button") + "\",";
  payload += "\"trigger_capture_url\":\"" + jsonEscape(localBaseUrl + "/trigger-capture") + "\",";
  payload += "\"mac\":\"" + jsonEscape(WiFi.macAddress()) + "\",";
  payload += "\"rssi\":" + String(WiFi.RSSI()) + ",";
  payload += "\"reported_by\":\"esp32\"";
  payload += "}";

  WiFiClient client;
  HTTPClient http;
  http.setTimeout(5000);
  if (!http.begin(client, endpointUrl)) {
    logf("Camera registry failed to start HTTP client: %s", endpointUrl.c_str());
    lastCameraRegistryReportMs = now;
    lastCameraRegistryReportOk = false;
    return;
  }

  http.addHeader("Content-Type", "application/json");
  int statusCode = http.POST(payload);
  String responseBody = http.getString();
  http.end();

  lastCameraRegistryReportMs = now;
  lastCameraRegistryReportOk = statusCode >= 200 && statusCode < 300;

  if (lastCameraRegistryReportOk) {
    logf("Camera registry updated: %s -> %s", endpointUrl.c_str(), localBaseUrl.c_str());
  } else {
    logf("Camera registry update failed (%d): %s", statusCode, responseBody.c_str());
  }
}

static void beginOtaService() {
  if (!wifiReady) {
    return;
  }

  ArduinoOTA.setHostname(deviceHostname.c_str());
  ArduinoOTA.onStart([]() {
    logf("ArduinoOTA start");
  });
  ArduinoOTA.onEnd([]() {
    logf("ArduinoOTA end");
  });
  ArduinoOTA.onProgress([](unsigned int progress, unsigned int total) {
    static unsigned int lastPercent = 255;
    unsigned int percent = total == 0 ? 0 : (progress * 100U) / total;
    if (percent != lastPercent && percent % 10 == 0) {
      lastPercent = percent;
      logf("ArduinoOTA progress: %u%%", percent);
    }
  });
  ArduinoOTA.onError([](ota_error_t error) {
    logf("ArduinoOTA error: %u", (unsigned int) error);
  });
  ArduinoOTA.begin();
  logf("ArduinoOTA ready.");
}

static void beginTelnetServer() {
  telnetServer.begin();
  telnetServer.setNoDelay(true);
  logf("Telnet logging ready on port %u", TELNET_PORT);
}

static void handleTelnetClients() {
  if (telnetServer.hasClient()) {
    WiFiClient incoming = telnetServer.available();
    bool assigned = false;

    for (size_t i = 0; i < TELNET_CLIENT_SLOTS; i++) {
      if (!telnetClients[i] || !telnetClients[i].connected()) {
        if (telnetClients[i]) {
          telnetClients[i].stop();
        }
        telnetClients[i] = incoming;
        telnetClients[i].println("FORTIROOM ESP32-CAM Telnet log connected.");
        assigned = true;
        break;
      }
    }

    if (!assigned) {
      incoming.println("Telnet log full.");
      incoming.stop();
    }
  }

  for (size_t i = 0; i < TELNET_CLIENT_SLOTS; i++) {
    if (telnetClients[i] && !telnetClients[i].connected()) {
      telnetClients[i].stop();
    }
    while (telnetClients[i] && telnetClients[i].available()) {
      telnetClients[i].read();
    }
  }
}

void setup() {
  Serial.begin(115200);
  pinMode(TRIGGER_PIN, INPUT_PULLUP);
  initFlashLed();
  setFlashLed(false);
  delay(1000);
  Serial.println();
  pushLogLine("FORTIROOM ESP32-CAM booting...");
  pushLogLine("Initializing camera...");

  if (!initCamera()) {
    pushLogLine("Camera init failed. Check board model and camera pin mapping.");
    while (true) {
      delay(1000);
    }
  }

  loadConfig();
  registerRoutes();

  if (!connectToSavedWiFi()) {
    startPortalMode();
  }

  server.begin();
  pushLogLine("HTTP server started.");

  if (wifiReady) {
    beginOtaService();
    beginTelnetServer();
    logf("Capture URL: http://%s/capture", WiFi.localIP().toString().c_str());
    logf("Camera registry endpoint: %s%s", getServerBaseUrl().c_str(), CAMERA_REGISTRY_PATH);
    reportCameraRegistration(true);
  } else {
    logf("Portal URL: http://%s/", WiFi.softAPIP().toString().c_str());
  }
}

void loop() {
  server.handleClient();

  if (portalMode) {
    dnsServer.processNextRequest();
    unsigned long nowBlink = millis();
    if (lastPortalBlinkMs == 0 || (nowBlink - lastPortalBlinkMs) >= PORTAL_BLINK_INTERVAL_MS) {
      lastPortalBlinkMs = nowBlink;
      blinkPortalWaitingPattern();
    }
  } else if (wifiReady) {
    ArduinoOTA.handle();
    handleTelnetClients();
    reportCameraRegistration(false);
  }

  bool currentTriggerState = digitalRead(TRIGGER_PIN);
  unsigned long now = millis();

  if (lastTriggerState == HIGH &&
      currentTriggerState == LOW &&
      (now - lastTriggerMs) > TRIGGER_DEBOUNCE_MS &&
      (now - lastTriggerMs) > TRIGGER_COOLDOWN_MS) {
    lastTriggerMs = now;
    buttonTriggerAtMs = now;
    bool captured = storeTriggeredCapture();
    buttonTriggerPending = captured;
    pushLogLine(captured
      ? "Physical trigger pressed. Stored snapshot for browser verification."
      : "Physical trigger pressed, but snapshot capture failed.");
  }

  lastTriggerState = currentTriggerState;
}
