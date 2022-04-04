<?php

namespace App\Services;

use App\Entity\Diary;
use App\Services\AuthorToDiaryService;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

class DiaryService {
    private $managerRegistry;

    public function __construct(ManagerRegistry $mr)
    {
        $this->managerRegistry = $mr;
    }

    public function addDiary($number, $title, $from_date, $to_date, $part_number) {
        $entityManager = $this->managerRegistry->getManager();

        $diary = new Diary();
        $diary->setNumber($number);
        $diary->setTitle($title);
        $diary->setFromDate($from_date);
        $diary->setToDate($to_date);
        $diary->setPartNumber($part_number);

        $entityManager->persist($diary);
        $entityManager->flush();

        return $diary->getId();
    }

    public function addDiaryIfNotExists($number, $title, $from_date, $to_date, $part_number) {
        $diary = $this->loadDiary($number);

        if(NULL === $diary) {
            return $this->addDiary($number, $title, $from_date, $to_date, $part_number);
        }

        die('Das Tagebuch mit der Nummer ' . $number . ' und dem Teil ' . $part_number . ' ist bereits eingetragen. Verarbeitung abgebrochen.');
    }

    public function upsert($number, $title, $from_date, $to_date, $part_number) {
        $diary = $this->loadDiary($number);

        if(NULL === $diary) {
            return $this->addDiary($number, $title, $from_date, $to_date, $part_number);
        }

        return $diary->getId();
    }

    public function loadDiary($number) {
        $entityManager = $this->managerRegistry->getManager();

        $diary = $entityManager->getRepository(Diary::class)->findOneBy([
            'number' => $number
        ]);

        return $diary;
    }

    public function loadDiariesArray($index, $limit) {
        $entityManager = $this->managerRegistry->getManager();

        $query = $entityManager->createQuery(
            'SELECT d
            FROM App\Entity\Diary d
            ORDER BY d.id ASC'
        )
        ->setFirstResult((int)$index)
        ->setMaxResults((int)$limit);

        $diaries = $query->getResult(Query::HYDRATE_ARRAY);

        foreach($diaries as $index => $diary) {
            $authorToDiaryService = new AuthorToDiaryService($this->managerRegistry);
            $authorIds = $authorToDiaryService->loadAuthorsArray($diary['id']);
            $diaries[$index]['author_ids'] = $authorIds;
        }

        return $diaries;
    }
}