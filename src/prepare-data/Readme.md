# Prepare Data

Ein in Symfony geschriebener Importer, der die Daten aus den Tagebüchern parsen, und in eine MySQL Datebank überführen kann.

Teilt Daten auf in:

* Autoren
* Tagebücher
* Beiträge (mit Volltext)
* Zeilen der Beiträge

Soll zukünftig noch:

* Daten in ein Wörterbuch übertragen, also die Daten in einem zweiten Schritt so verarbeiten, dass einzelne Worte gezählt, und ihre Verlinkung in die Beiträge in einer oder zwei Datenbank-Tabellen gespeichert werden.

## System-Requirements

* PHP ab 8.1, Apache, MySQL 5.7 oder Maria DB 10.x
* Composer zur Installation (basiert auf Symfony), installiert abhängigkeiten mit composer install
* 

# Installation

1. Verzeichnis erstellen
2. Projekt in Verzeichnis klonen
3. Composer install
4. MySQL Datenbank einrichten
5. MySQL Datenbankverbindung in .env einrichten, ggf. die .env.example als Vorlage verwenden.
6. In .env das App-Secret auf einen eigenen Wert setzen.
7. Datenbank initialisieren (php bin/console doctrine:schema:update --force)
8. Ggf. Apache Config so einstellen, dass der Hostname auf /public zeigt.
9. Fertig.
