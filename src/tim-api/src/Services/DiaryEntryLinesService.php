<?php

namespace App\Services;

use App\Entity\DiaryEntryLines;
use Doctrine\Persistence\ManagerRegistry;

class DiaryEntryLinesService {
    private $managerRegistry;

    public function __construct(ManagerRegistry $mr)
    {
        $this->managerRegistry = $mr;
        
    }

    public function addDiaryEntryLine($diary_entry_id, $sort_order, $content) {
        $entityManager = $this->managerRegistry->getManager();

        $diaryEntryLines = new DiaryEntryLines();
        $diaryEntryLines->setDiaryEntryId($diary_entry_id);
        $diaryEntryLines->setSortOrder($sort_order);
        $diaryEntryLines->setContent($content);

        $entityManager->persist($diaryEntryLines);
        $entityManager->flush();

        return $diaryEntryLines->getId();
    }
}