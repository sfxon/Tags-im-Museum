<?php

namespace App\Services;

use App\Entity\AuthorToDiary;
use Doctrine\Persistence\ManagerRegistry;

class AuthorToDiaryService {
    private $managerRegistry;

    public function __construct(ManagerRegistry $mr)
    {
        $this->managerRegistry = $mr;
    }

    public function insert($authorId, $diaryId) {
        $entityManager = $this->managerRegistry->getManager();

        $authorToDiary = new AuthorToDiary();
        $authorToDiary->setAuthorId($authorId);
        $authorToDiary->setDiaryId($diaryId);

        $entityManager->persist($authorToDiary);
        $entityManager->flush();

        return $authorToDiary->getId();
    }

    public function upsert($authorId, $diaryId) {
        $authorToDiary = $this->load($authorId, $diaryId);

        if(NULL === $authorToDiary) {
            return $this->insert($authorId, $diaryId);
        }

        return $authorToDiary->getId();
    }

    public function load($authorId, $diaryId) {
        $entityManager = $this->managerRegistry->getManager();

        $authorToDiary = $entityManager->getRepository(AuthorToDiary::class)->findOneBy([
            'author_id' => $authorId,
            'diary_id' => $diaryId
        ]);

        return $authorToDiary;
    }
}