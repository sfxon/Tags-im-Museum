<?php

namespace App\DTO;

class DiaryEntryHeading {
    private $weekday;
    private $entryDate;
    private $author;

    public function getWeekday() {
        return $this->weekday;
    }

    public function setWeekday($weekday) {
        $this->weekday = $weekday;
    }

    public function getEntryDate() {
        return $this->entryDate;
    }

    public function setEntryDate($entryDate) {
        $this->entryDate = $entryDate;
    }

    public function setEntryDateByString($entryDate) {
        $this->entryDate = new \DateTime($entryDate);
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }
}