<?php

namespace App\Services;

use App\Entity\Diary;
use Doctrine\Persistence\ManagerRegistry;

class DiaryService {
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        
    }

    public function addDiary($number, $title, $from_date, $to_date, $part_number) {
        $entityManager = $this->doctrine->getManager();

        $diary = new Diary();
        $diary->setNumber($number);
        $diary->setTitle($title);
        $diary->setFromDate($from_date);
        $diary->setToDate($to_date);
        $diary->setPartNumber($part_number);

        $entityManager->persist($diary);
        $entityManager()->flush();

        return $diary->getId();
    }
}