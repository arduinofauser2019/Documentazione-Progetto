#include <DHT.h>
#include <DHT_U.h>

#include <Adafruit_Sensor.h>

/* ATTENZIONE: Sono richieste le seguenti librerie Arduino:
 * DHT Sensor Library: https://github.com/adafruit/DHT-sensor-library
 * Adafruit Unified Sensor Lib: https://github.com/adafruit/Adafruit_Sensor
 */

#include "DHT.h"

#define DHTPIN 2     // PIN digitale connesso al PIN "dati" del Sensore DHT 11

#define PHOTOSENSPIN A2

#define DHTTYPE DHT11 // Definizione tipo di sensore utilizzato
DHT dht(DHTPIN, DHTTYPE);

void setup() {
  pinMode(PHOTOSENSPIN, INPUT); // Definizione del PIN di input per il sensore di luminosità.
  Serial.begin(9600); // Baud Rate pari a 9600
  dht.begin(); // Inizializzazione lettura
}

void loop() {
  delay(500); // Attesa di 500 ms tra le misurazioni
  /*
   * NB: La lettura delle misure impiega almeno 250 ms e può richiedere anche 2 secondi, per via di problemi di bufferizzazione.
   */
   
  float umidita = dht.readHumidity(); // Lettura Umidità (metodo già pronto)
  float temperatura = dht.readTemperature(); // Lettura Temperatura (metodo già pronto, gradi Celsius)

  // Se la lettura è fallita...
  if (isnan(umidita) || isnan(temperatura)) {
    Serial.println(F("LETTURA FALLITA!")); // ...lo indico sulla porta seriale
    return;
  }
  
  // Calcola l'indice di calore in Gradi Celsius
  float indiceCalore = dht.computeHeatIndex(temperatura, umidita, false);

  int luminosita = 100 - (analogRead(PHOTOSENSPIN)/6);

  Serial.print(F("Umidita': "));
  Serial.print(umidita);
  Serial.print(F(" % Temperatura: "));
  Serial.print(temperatura);
  Serial.print(F(" °C "));
  Serial.print(F("Indice di calore: "));
  Serial.print(indiceCalore);
  Serial.print(F(" °C "));
  Serial.print(F("Luminosita': "));
  Serial.print(luminosita);
  Serial.println(F(" %"));
}
