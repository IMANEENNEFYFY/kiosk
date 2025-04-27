<?php

namespace App\Repository;

use App\Entity\CartePrepayee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartePrepayee>
 */
class CartePrepayeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartePrepayee::class);
    }

    public function findOneByCode(string $codeCarte): ?CartePrepayee
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.codeCarte = :codeCarte')
            ->andWhere('c.actif = true')
            ->setParameter('codeCarte', $codeCarte)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return CartePrepayee[] Returns an array of CartePrepayee objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CartePrepayee
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
