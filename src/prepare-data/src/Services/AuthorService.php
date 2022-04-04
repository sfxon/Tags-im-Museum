<?php

namespace App\Services;

use App\Entity\Author;
use Doctrine\Persistence\ManagerRegistry;

class AuthorService {
    private $managerRegistry;

    public function __construct(ManagerRegistry $mr)
    {
        $this->managerRegistry = $mr;
    }

    public function addAuthor($title, $firstname, $lastname) {
        $entityManager = $this->managerRegistry->getManager();

        $author = new Author();
        $author->setTitle($title);
        $author->setFirstname($firstname);
        $author->setLastname($lastname);

        $entityManager->persist($author);
        $entityManager->flush();

        return $author->getId();
    }

    public function addAuthorIfNotExists($title, $firstname, $lastname) {
        $author = $this->loadAuthorByName($firstname, $lastname);

        if(NULL === $author) {
            return $this->addAuthor($title, $firstname, $lastname);
        }

        return $author->getId();
    }

    public function loadAuthor($title, $firstname, $lastname) {
        $entityManager = $this->managerRegistry->getManager();

        $author = $entityManager->getRepository(Author::class)->findOneBy([
            'title' => $title,
            'firstname' => $firstname,
            'lastname' => $lastname
        ]);

        return $author;
    }

    public function loadAuthorByName($firstname, $lastname) {
        $entityManager = $this->managerRegistry->getManager();

        $author = $entityManager->getRepository(Author::class)->findOneBy([
            'firstname' => $firstname,
            'lastname' => $lastname
        ]);

        return $author;
    }
}