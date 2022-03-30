<?php

namespace App\DTO;

use App\Services\DateHelper;
use App\DTO\Author;

class DiaryHeading {
    private $Id = null;
    private $Number = null;
    private $Title = null;
    private $From = null;
    private $To = null;
    private $Authors = null;
    private $Year = null;
    private $Part = null;

    public function getId() {
        return $this->Id;
    }

    public function getNumber() {
        return $this->Number;
    }

    public function setNumber($number) {
        $this->Number = $number;
    }

    public function getTitle() {
        return $this->Title;
    }

    public function setTitle($title) {
        $this->Title = $title;
    }

    public function getFromTo() {
        if(null === $this->From || null === $this->To) {
            return null;
        }

        return $this->From . " - " . $this->To;
    }

    public function getFrom() {
        return $this->From;
    }

    public function getTo() {
        return $this->To;
    }

    public function setFromToFromString($input) {
        $input = str_replace("Vom", "", $input);
        $parts = explode("bis", $input);

        if(!is_array($parts) && count($parts) != 2) {
            throw "Error processing From To string";
        }

        $from = trim($parts[0]);
        $to = trim($parts[1]);
        $to = rtrim($to, ".");

        $from = DateHelper::parseFullTextualDate($from);
        $to = DateHelper::parseFullTextualDate($to);

        $this->From = $from;
        $this->To = $to;
    }

    public function getAuthors() {
        return $this->Authors;
    }

    public function setAuthors($authors) {
        if(!is_array($authors)) {
            throw new \Exception("Authors has to provide an array.");
        }
        
        $this->Authors = $authors;
    }

    public function setAuthorsFromString($input) {
        $input = str_replace("Geführt von ", "", $input);
        $input = rtrim($input, ".");

        $parts = explode(",", $input);

        if(!is_array($parts)) {
            return false;
        }

        if(count($parts) == 1) {
            $parts = explode(" und ", $parts[0]);
        }

        if(count($parts) == 0) {
            return false;
        }

        $authors = [];

        foreach($parts as $authorPart) {
            // Unterteile anhand von Leerzeichen.
            $nameParts = explode(" ", trim($authorPart));

            if(!is_array($nameParts)) {
                throw new \Exception("Fehler beim Aufteilen eines Authoren-Strings");
            }

            $count = count($nameParts);

            if($count != 2 && $count != 3) {
                throw new \Exception("Authoren-Name enthält weder 2, noch 3 Bestandteile");
            }

            // Falls drei Bestandteile: Parse Titel (in eckigen Klammern)
            $author = new Author();
            
            if($count == 3) {
                $author->setTitle($nameParts[0]);
                $author->setFirstname($nameParts[1]);
                $author->setLastname($nameParts[2]);
            } else {
                $author->setFirstname($nameParts[0]);
                $author->setLastname($nameParts[1]);
            }

            // Author zur Liste der Autoren hinzufügen
            $authors[] = $author;
        }

        if(count($authors) == 0) {
            throw new \Exception("Keine Authoren gefunden");
        }

        $this->Authors = $authors;
    }

    public function getYearAndPart() {
        if(null === $this->Year || null === $this->Part) {
            return null;
        }
        
        return $this->Year . ', Teil ' . $this->Part;
    }

    public function setYearAndPartFromString($input) {
        $parts = explode(',', $input);

        if(count($parts) !== 2) {
            throw new \Exception("Jahr und Teil-Zeile enthält kein Komma.");
        }

        $this->Year = $parts[0];
        $this->Part = $parts[1];
        $this->Part = trim(str_replace("Teil ", "", $this->Part));
    }
}