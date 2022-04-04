<?php

namespace App\Services;

use App\Entity\AuthorToDiary;
use Doctrine\ORM\Query;
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

    public function loadAuthorIdsArray($diaryId) {
        $entityManager = $this->managerRegistry->getManager();

        $query = $entityManager->createQuery(
            'SELECT atd
            FROM App\Entity\AuthorToDiary atd
            WHERE atd.diary_id = :diaryId
            ORDER BY atd.id ASC'
        )
        ->setParameter(':diaryId', $diaryId);
        
        // returns an array of Product objects
        $results = $query->getResult(Query::HYDRATE_ARRAY);

        $retval = array();

        foreach($results as $res) {
            $retval[] = $res['author_id'];
        }

        return $retval;
    }

    public function loadAuthorsArray($diaryId) {
        $entityManager = $this->managerRegistry->getManager();

        $query = $entityManager->createQueryBuilder() 
            ->select('a')
            ->from('App\Entity\AuthorToDiary', 'atd')
            ->leftJoin('App\Entity\Author', 'a', 'WITH', 'atd.author_id = a.id')
            ->where('atd.diary_id = :diaryId')
            ->orderBy('atd.id', 'ASC')
            ->setParameter(':diaryId', $diaryId);
        
        // returns an array of Product objects
        $results = $query->getQuery()->getResult(Query::HYDRATE_ARRAY);

        return $results;
    }
}