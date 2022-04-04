# TIM-API (Tags im Museum - Application Programming Interface)

Eine in Symfony geschriebene API, die Daten aus den Tagebüchern als REST-API bereitstellt.

# Verfügbare Routen

```
/api/v0.1/authors
/api/v0.1/diaries
/api/v0.1/diary/{diary_id}/entries
/api/v0.1/diaries/entries?keyword={keyword}
```

# Automatische Limits
Alle Routen verfügen über Paginierung.
Zur Paginierung können diese Parameter als Query-Parameter (GET) verwendet werden:

**index**<br />
Wird verwendet, um den index festzulegen, ab dem die Daten ausgegeben werden sollen.
0 ist der erste Eintrag. Die Seitenzahl muss selbst berechnet und übergeben werden.
1 gibt also nicht die zweite Seite aus, sondern den zweiten Eintrag in der Datenbank.

**limit**<br />
Gibt an, wieviele Einträge maximal ausgelesen werden sollen.

Die Standard-Limit-Größe ist 50.<br />
Bei Diary Einträgen beträgt die Standard-Limit Größe aktuell allerdings 500 Einträge.

**Beispiel**<br />
Ein Beispiel mit index und limit:

```
/api/v0.1/diary/{diary_id}/entries?limit=10&index=15
```

Es werden 10 Einträge geladen, ab dem 15 Eintrag in der Datenbank.

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

## Hinweis für Hetzner

Ich musste mir auf einem Hetzner Web-Hosting zunächst Composer installieren, damit ich die Pfade mit
composer dump-autoload neu schreiben konnte.

Dazu bin ich im SSH-Root Folder des SSH-Users so vorgegangen:

mkdir ~/bin
mkdir ~/bin/composer
cd ~/bin/composer
wget https://getcomposer.org/composer.phar
echo "# composer alias" >> ~/.bashrc
echo "alias composer='php -d allow_url_fopen=on ~/bin/composer/composer.phar'" >> ~/.bashrc
echo "alias php='/usr/bin/php80'" >> ~/.bashrc
source ~/.bashrc
composer -h

Die Idee dazu kommt aus dieser Anleitung:
https://gist.github.com/derralf/ae6e8fc37fdf8e8e9a213305c88af846
