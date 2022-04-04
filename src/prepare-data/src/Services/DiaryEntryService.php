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

    public function addDiaryEntry($diary_id, $sort_order, $date_of_entry, $page_number, $content_text) {
        $entityManager = $this->managerRegistry->getManager();

        $diaryEntry = new diaryEntry();
        $diaryEntry->setDiaryId($diary_id);
        $diaryEntry->setSortOrder($sort_order);
        $diaryEntry->setDateOfEntry($date_of_entry);
        $diaryEntry->setContentText($content_text);
        $diaryEntry->setPageNumber($page_number);

        $entityManager->persist($diaryEntry);
        $entityManager->flush();

        return $diaryEntry->getId();
    }
}