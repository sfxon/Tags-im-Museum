<?php

namespace App\DTO;

class DiaryHeading {
    private $Id = null;
    private $Number = null;
    private $Title = null;
    private $FromTo = null;
    private $Authors = null;

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
        return $this->FromTo;
    }

    public function setFromTo($fromTo) {
        $this->FromTo = $fromTo;
    }

    public function getAuthors() {
        return $this->Authors;
    }

    public function setAuthors($authors) {
        $this->Authors = $authors;
    }
}