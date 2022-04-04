<?php

namespace App\Services;

use App\Entity\AuthorToDiaryEntry;
use Doctrine\Persistence\ManagerRegistry;

class AuthorToDiaryEntryService {
    private $managerRegistry;

    public function __construct(ManagerRegistry $mr)
    {
        $this->managerRegistry = $mr;
    }

    public function insert($authorId, $diaryEntryId) {
        $entityManager = $this->managerRegistry->getManager();

        $authorToDiaryEntry = new AuthorToDiaryEntry();
        $authorToDiaryEntry->setAuthorId($authorId);
        $authorToDiaryEntry->setDiaryEntryId($diaryEntryId);

        $entityManager->persist($authorToDiaryEntry);
        $entityManager->flush();

        return $authorToDiaryEntry->getId();
    }

    public function upsert($authorId, $diaryEntryId) {
        $authorToDiaryEntry = $this->load($authorId, $diaryEntryId);

        if(NULL === $authorToDiaryEntry) {
            return $this->insert($authorId, $diaryEntryId);
        }

        return $authorToDiaryEntry->getId();
    }

    public function load($authorId, $diaryEntryId) {
        $entityManager = $this->managerRegistry->getManager();

        $authorToDiaryEntry = $entityManager->getRepository(AuthorToDiaryEntry::class)->findOneBy([
            'author_id' => $authorId,
            'diary_entry_id' => $diaryEntryId
        ]);

        return $authorToDiaryEntry;
    }
}