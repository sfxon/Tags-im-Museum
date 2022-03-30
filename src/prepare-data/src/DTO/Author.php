<?php

namespace App\DTO;

class Author {
    private $Id = null;
    private $Title = null;
    private $Firstname = null;
    private $Lastname = null;

    public function getId() {
        return $this->Id;
    }

    public function setId($id) {
        $this->Id = $id;
    }

    public function getTitle() {
        return $this->Title;
    }

    public function setTitle($title) {
        $this->Title = $title;
    }

    public function getFirstname() {
        return $this->Firstname;
    }

    public function setFirstname($firstname) {
        $this->Firstname = $firstname;
    }

    public function getLastname() {
        return $this->Lastname;
    }

    public function setLastname($lastname) {
        $this->Lastname = $lastname;
    }
}