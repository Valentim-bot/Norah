#include <WiFi.h>
#include <HTTPClient.h>
#include <Arduino_JSON.h>
#include <Wire.h>
#include <Adafruit_MLX90614.h>

// ===== I2C Setup for Two Sensors =====
TwoWire I2Cone = TwoWire(0);   // Default I2C (GPIO21 SDA, GPIO22 SCL)
TwoWire I2Ctwo = TwoWire(1);   // Second I2C (GPIO25 SDA, GPIO26 SCL)

//Run this on webserver(google chrome):http://localhost/mlxsensornorah/test/index.php
//http://192.168.11.131/mlxsensornorah/test/index.php


// ===== MLX90614 Sensors =====
Adafruit_MLX90614 mlx1 = Adafruit_MLX90614();
Adafruit_MLX90614 mlx2 = Adafruit_MLX90614();

// ===== Control pins (DC motors) =====
const int dcmotor1 = 4;
const int dcmotor2 = 15;
const int IN1 = 5;
const int IN3 = 18;


// ===== Wi-Fi Settings =====
const char* ssid     = "La_Fibre_dOrange_73D3";
const char* password = "ATN2N95UC7DGKZR99G";

// ===== Variables =====
float amb1=0.0, obj1=0.0;
float amb2=0.0, obj2=0.0;

int proportionalSpeed1=0, proportionalSpeed2=0;
int appliedSpeed1=0, appliedSpeed2=0;

int scaledSpeed1=0, scaledSpeed2=0;
int scaledAmb1=0, scaledAmb2=0;

// Manual override
bool manualOverride1 = false;
bool manualOverride2 = false;
int manualSpeed1 = 0;
int manualSpeed2 = 0;
unsigned long lastManualTime1 = 0;
unsigned long lastManualTime2 = 0;
const unsigned long manualTimeout = 8000;

// HTTP POST variables
String postData = "";
String payload = "";

// Speed buttons from MySQL
int speed1_1=0, speed2_1=0, speed3_1=0, speed4_1=0; // sensor 1
int speed1_2=0, speed2_2=0, speed3_2=0, speed4_2=0; // sensor 2

// Timing
unsigned long lastSendTime = 0;
const unsigned long sendInterval = 5000; // send every 5 sec

// ---- Helpers ----
int parseFlag(JSONVar obj, const char* key) {
  if (!obj.hasOwnProperty(key)) return 0;
  JSONVar v = obj[key];
  String t = JSON.typeof(v);
  if (t == "boolean") return ((bool)v) ? 1 : 0;
  else if (t == "number") return ((int)v != 0) ? 1 : 0;
  else if (t == "string") {
    String s = (const char*)v; s.trim(); s.toUpperCase();
    return (s == "1" || s == "ON" || s == "TRUE") ? 1 : 0;
  }
  return 0;
}

void analogWrite(uint8_t pin, int value) {
  static int channel = 0;
  static bool initialized[16] = {false};
  if (!initialized[channel]) {
    ledcSetup(channel, 5000, 8); 
    ledcAttachPin(pin, channel);
    initialized[channel] = true;
  }
  value = constrain(value, 0, 255);
  ledcWrite(channel, value);
  channel++;
  if(channel>15) channel=0;
}

void setup() {
  Serial.begin(115200);
  pinMode(dcmotor1, OUTPUT);
  pinMode(dcmotor2, OUTPUT);
  pinMode(IN1, OUTPUT);
  pinMode(IN3, OUTPUT);
  digitalWrite(IN1, HIGH);
  digitalWrite(IN3, HIGH);

  // Start I2C
  I2Cone.begin(21, 22);
  I2Ctwo.begin(25, 26);

  // Initialize sensors
  if (!mlx1.begin(0x5A, &I2Cone)) {
    Serial.println("Error connecting to MLX90614 #1.");
    while(1);
  }
  if (!mlx2.begin(0x5A, &I2Ctwo)) {
    Serial.println("Error connecting to MLX90614 #2.");
    while(1);
  }
  Serial.println("Both MLX sensors initialized successfully.");

  // Connect Wi-Fi
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  Serial.print("Connecting");
  int timeout = 40;
  while (WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    delay(500);
    if (timeout-- <= 0) ESP.restart();
  }
  Serial.println("\nWiFi connected");
  Serial.print("IP: "); Serial.println(WiFi.localIP());
}

void fetchSpeeds() {
  HTTPClient http;
  postData = "id=esp32_01";
  http.begin("http://192.168.11.131/mlxsensornorah/test/getSpeeds.php");
  http.addHeader("Content-Type","application/x-www-form-urlencoded");

  int httpCode = http.POST(postData);
  payload = http.getString();
  http.end();

  if (httpCode != 200) return;

  JSONVar doc = JSON.parse(payload);
  if (JSON.typeof(doc) == "undefined") return;

  // sensor 1
  speed1_1 = parseFlag(doc, "speed1_1");
  speed2_1 = parseFlag(doc, "speed2_1");
  speed3_1 = parseFlag(doc, "speed3_1");
  speed4_1 = parseFlag(doc, "speed4_1");

  // sensor 2
  speed1_2 = parseFlag(doc, "speed1_2");
  speed2_2 = parseFlag(doc, "speed2_2");
  speed3_2 = parseFlag(doc, "speed3_2");
  speed4_2 = parseFlag(doc, "speed4_2");
}

void setManualSpeed1(int newSpeed) {
  manualOverride1 = true;
  manualSpeed1 = newSpeed;
  lastManualTime1 = millis();
}

void setManualSpeed2(int newSpeed) {
  manualOverride2 = true;
  manualSpeed2 = newSpeed;
  lastManualTime2 = millis();
}

void loop() {
  // ==== Read MLX sensors ====
  amb1 = mlx1.readAmbientTempC();
  obj1 = mlx1.readObjectTempC();
  amb2 = mlx2.readAmbientTempC();
  obj2 = mlx2.readObjectTempC();

  Serial.println();
  Serial.println("===== Sensor Readings =====");
  Serial.print("Sensor 1 -> Ambient: "); Serial.print(amb1); Serial.print(" °C, Object: "); Serial.println(obj1);
  Serial.print("Sensor 2 -> Ambient: "); Serial.print(amb2); Serial.print(" °C, Object: "); Serial.println(obj2);

  // ==== Fetch speeds/buttons from MySQL ====
  fetchSpeeds();

  Serial.println("Fetched speed buttons:");
  Serial.printf("Sensor1: s1:%d s2:%d s3:%d s4:%d\n", speed1_1, speed2_1, speed3_1, speed4_1);
  Serial.printf("Sensor2: s1:%d s2:%d s3:%d s4:%d\n", speed1_2, speed2_2, speed3_2, speed4_2);

  // ==== Manual override timeout ====
  if (manualOverride1 && millis() - lastManualTime1 > manualTimeout) {
    manualOverride1 = false;
    Serial.println("Sensor 1 -> Manual override expired -> AUTO mode");
  }
  if (manualOverride2 && millis() - lastManualTime2 > manualTimeout) {
    manualOverride2 = false;
    Serial.println("Sensor 2 -> Manual override expired -> AUTO mode");
  }

  // ==== Check manual buttons ====
  if (speed1_1) setManualSpeed1(63);
  else if (speed2_1) setManualSpeed1(126);
  else if (speed3_1) setManualSpeed1(189);
  else if (speed4_1) setManualSpeed1(252);

  if (speed1_2) setManualSpeed2(63);
  else if (speed2_2) setManualSpeed2(126);
  else if (speed3_2) setManualSpeed2(189);
  else if (speed4_2) setManualSpeed2(252);

  // ==== Auto vs Manual speed logic ====
  if (manualOverride1) appliedSpeed1 = manualSpeed1;
  else {
    proportionalSpeed1 = (int)(obj1 * 5.0);
    proportionalSpeed1 = constrain(proportionalSpeed1,0,255);
    if(obj1>=50) appliedSpeed1=255;
    else if(obj1>=45) appliedSpeed1=191;
    else if(obj1>=40) appliedSpeed1=128;
    else if(obj1>=35) appliedSpeed1=64;
    else appliedSpeed1=0;
  }

  if (manualOverride2) appliedSpeed2 = manualSpeed2;
  else {
    proportionalSpeed2 = (int)(obj2 * 5.0);
    proportionalSpeed2 = constrain(proportionalSpeed2,0,255);
    if(obj2>=50) appliedSpeed2=255;
    else if(obj2>=45) appliedSpeed2=191;
    else if(obj2>=40) appliedSpeed2=128;
    else if(obj2>=35) appliedSpeed2=64;
    else appliedSpeed2=0;
  }

  // ==== Scale values for MySQL ====
  scaledSpeed1 = map(proportionalSpeed1,0,255,0,100);
  scaledSpeed2 = map(proportionalSpeed2,0,255,0,100);
  scaledAmb1   = map((int)amb1,0,70,0,70);
  scaledAmb2   = map((int)amb2,0,70,0,70);

  Serial.println("Motor Speeds:");
  Serial.print("Sensor 1 -> Applied: "); Serial.print(appliedSpeed1); 
  Serial.print(", Proportional: "); Serial.println(scaledSpeed1);
  Serial.print("Sensor 2 -> Applied: "); Serial.print(appliedSpeed2); 
  Serial.print(", Proportional: "); Serial.println(scaledSpeed2);

  // Apply PWM to motors
  analogWrite(dcmotor1, appliedSpeed1);
  analogWrite(dcmotor2, appliedSpeed2);

  // ==== Send data to MySQL ====
  if(millis() - lastSendTime >= sendInterval) {
    lastSendTime = millis();
    HTTPClient http;
    postData = "id=esp32_01";
    postData += "&amb1=" + String(scaledAmb1);
    postData += "&obj1=" + String(obj1);
    postData += "&speed1=" + String(appliedSpeed1);
    postData += "&proportionalSpeed1=" + String(scaledSpeed1);

    postData += "&amb2=" + String(scaledAmb2);
    postData += "&obj2=" + String(obj2);
    postData += "&speed2=" + String(appliedSpeed2);
    postData += "&proportionalSpeed2=" + String(scaledSpeed2);

    Serial.println("Sending data to MySQL...");
    Serial.println(postData);

    http.begin("http://192.168.11.131/mlxsensornorah/test/updateMLXdata.php");
    http.addHeader("Content-Type","application/x-www-form-urlencoded");

    int httpCode = http.POST(postData);
    payload = http.getString();
    Serial.print("httpCode : "); Serial.println(httpCode);
    Serial.print("payload  : "); Serial.println(payload);
    http.end();
  }

  delay(10);
}
