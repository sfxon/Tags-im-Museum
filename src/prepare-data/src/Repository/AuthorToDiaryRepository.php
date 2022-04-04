<?php

namespace App\Repository;

use App\Entity\AuthorToDiary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuthorToDiary|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthorToDiary|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthorToDiary[]    findAll()
 * @method AuthorToDiary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorToDiaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthorToDiary::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(AuthorToDiary $entity, bool $flush = true): void
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
    public function remove(AuthorToDiary $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return AuthorToDiary[] Returns an array of AuthorToDiary objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AuthorToDiary
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
