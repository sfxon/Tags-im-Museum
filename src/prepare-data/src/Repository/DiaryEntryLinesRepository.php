<?php

namespace App\Repository;

use App\Entity\DiaryEntryLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DiaryEntryLines|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiaryEntryLines|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiaryEntryLines[]    findAll()
 * @method DiaryEntryLines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiaryEntryLinesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiaryEntryLines::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(DiaryEntryLines $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(DiaryEntryLines $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return DiaryEntryLines[] Returns an array of DiaryEntryLines objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DiaryEntryLines
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
