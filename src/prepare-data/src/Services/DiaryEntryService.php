<?php

namespace App\Services;

use App\Entity\DiaryEntry;
use Doctrine\Persistence\ManagerRegistry;

class DiaryEntryService {
    private $managerRegistry;

    public function __construct(ManagerRegistry $mr)
    {
        $this->managerRegistry = $mr;
        
    }

    public function addDiaryEntry($diary_id, $sort_order, $date_of_entry, $content_text) {
        $entityManager = $this->managerRegistry->getManager();

        $diaryEntry = new diaryEntry();
        $diaryEntry->setDiaryId($diary_id);
        $diaryEntry->setSortOrder($sort_order);
        $diaryEntry->setDateOfEntry($date_of_entry);
        $diaryEntry->setContentText($content_text);

        $entityManager->persist($diaryEntry);
        $entityManager->flush();

        return $diaryEntry->getId();
    }
}