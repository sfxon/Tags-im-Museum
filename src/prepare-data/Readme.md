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

# Arbeiten mit dem Importer

## Initialen Datenbestand erstellen

Der Reihe nach diese Befehle ausführen, um eine initiale Datenbank zu erhalten.
Voraussetzung: Das Projekt muss lokal unter der Domain tags-im-museum.localhost erreichbar sein.

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=01.txt&sort_order=1

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=02a.txt&sort_order=1

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=02b.txt&sort_order=147

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=03.txt&sort_order=1

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=04.txt&sort_order=1

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=05.txt&sort_order=1

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=06.txt&sort_order=1

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=07.txt&sort_order=1

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=08.txt&sort_order=1

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=09.txt&sort_order=1

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=10a.txt&sort_order=1&set_all_authors=true

http://tags-im-museum.localhost/addDiaryEntriesFromFile?filename=10b.txt&sort_order=419&set_all_authors=true&short_dates=true

## Alle Daten entfernen (Tabellen zurücksetzen)

Diese Anweisungen müssen direkt in der MySQL Datenbank ausgeführt werden.
Hierbei werden auch die Auto-Increment Indizes zurückgesetzt.

```
truncate table author;
truncate table author_to_diary;
truncate table author_to_diary_entry;
truncate table diary;
truncate table diary_entry;
truncate table diary_entry_lines;
```