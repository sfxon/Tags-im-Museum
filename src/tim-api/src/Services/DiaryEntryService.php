<?php

namespace App\Services;

use App\Entity\DiaryEntry;
use Doctrine\ORM\Query;
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

    public function loadDiaryEntriesArray($diaryId, $index, $limit) {
        $entityManager = $this->managerRegistry->getManager();

        $query = $entityManager->createQuery(
            'SELECT de
            FROM App\Entity\DiaryEntry de
            WHERE de.diary_id = :diaryId
            ORDER BY de.sort_order ASC'
        )
        ->setParameter(':diaryId', $diaryId)
        ->setFirstResult((int)$index)
        ->setMaxResults((int)$limit);

        $diaryEntries = $query->getResult(Query::HYDRATE_ARRAY);

        foreach($diaryEntries as $index => $diaryEntry) {
            $authorToDiaryService = new AuthorToDiaryService($this->managerRegistry);
            $authorIds = $authorToDiaryService->loadAuthorsArray($diaryEntry['diary_id']);
            $diaryEntries[$index]['authors'] = $authorIds;
        }

        return $diaryEntries;
    }

    public function searchEntries($keyword, $index, $limit) {
        $entityManager = $this->managerRegistry->getManager();

        $query = $entityManager->createQuery(
            'SELECT de
            FROM App\Entity\DiaryEntry de
            WHERE de.content_text LIKE :keyword
            ORDER BY de.sort_order ASC'
        )
        ->setParameter(':keyword', "%" . $keyword. "%")
        ->setFirstResult((int)$index)
        ->setMaxResults((int)$limit);

        $diaryEntries = $query->getResult(Query::HYDRATE_ARRAY);

        foreach($diaryEntries as $index => $diaryEntry) {
            $authorToDiaryService = new AuthorToDiaryService($this->managerRegistry);
            $authorIds = $authorToDiaryService->loadAuthorsArray($diaryEntry['diary_id']);
            $diaryEntries[$index]['authors'] = $authorIds;
        }

        return $diaryEntries;
    }
}