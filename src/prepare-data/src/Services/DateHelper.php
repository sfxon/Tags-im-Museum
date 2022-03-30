<?php

namespace App\Services;

class DateHelper {
    // Parst ein Datum im Format "1. Januar 2022"
    // Avoids set_locale and date_parse_from_format,
    // since they are hard to handle on different server environments
    // (different results in different environments)
    public static function parseFullTextualDate($date) {
        $date = trim($date, '.');       // Remove a dot in the end
        $parts = explode('.', $date);

        if(!is_array($parts)) {
            return false;
        }

        if(count($parts) != 2) {
            return false;
        }

        // Tag rausfiltern
        $day = intval($parts[0]);

        // Zweiten Teil in Monat und Jahr aufdröseln
        $parts = explode(" ", trim($parts[1]));

        if(count($parts) != 2) {
            return false;
        }

        $month = trim($parts[0]);
        $year = trim($parts[1]);

        // Monat als Zahl ermitteln
        $month = DateHelper::getMonthByName($month);

        // SQL Format erzeugen
        $day = str_pad($day, 2, "0", STR_PAD_LEFT);
        $month = str_pad($month, 2, "0", STR_PAD_LEFT);
        
        $result = $year . "-" . $month . "-" . $day;

        return $result;
    }

    public static function getMonthByName($name) {
        $months = [ "Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"];

        if(in_array($name, $months)) {
            return (array_search($name, $months) + 1);
        }

        return false;
    }

    public static function getWeekdayByName($name) {
        $days = [ "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Sonnabend", "Sonntag" ];

        if(in_array($name, $days)) {
            return (array_search($name, $days) + 1);
        }

        return false;
    }

    public static function parseNumericTextualDate($dateString) {
        $format = "j. n. Y";
        $parsedDate = date_parse_from_format($format, $dateString);

        if($parsedDate['error_count'] == 0) {
            $year = $parsedDate['year'];
            $month = $parsedDate['month'];
            $day = $parsedDate['day'];

            // SQL Format erzeugen
            $day = str_pad($day, 2, "0", STR_PAD_LEFT);
            $month = str_pad($month, 2, "0", STR_PAD_LEFT);
            
            $result = $year . "-" . $month . "-" . $day;

            return $result;
        }

        return false;;
    }
}