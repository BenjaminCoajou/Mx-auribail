<?php

namespace App\Repository;

use App\Entity\Training;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Training|null find($id, $lockMode = null, $lockVersion = null)
 * @method Training|null findOneBy(array $criteria, array $orderBy = null)
 * @method Training[]    findAll()
 * @method Training[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Training::class);
    }


    // Find the next Training session for '1' Adults for '0' Childs
    public function findNextTraining($isAdult)
    {
        $now = new DateTime();

        $result =  $this->createQueryBuilder('t')
            ->andWhere('t.adult = :isAdult')
            ->andWhere('t.trainingDate > :now')
            ->orderBy('t.trainingDate', 'ASC')
            ->setParameter('isAdult', $isAdult )
            ->setParameter('now', $now )
            ->setMaxResults('1')
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    // /**
    //  * @return Training[] Returns an array of Training objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Training
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
