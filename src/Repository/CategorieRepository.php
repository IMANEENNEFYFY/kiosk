<?php

namespace App\Repository;

use App\Entity\Categorie;

use App\Entity\Espace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }
    public function findByEspace(int $espaceId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.espace = :espace')
            ->setParameter('espace', $espaceId)
            ->getQuery()
            ->getResult();
    }
    // src/Repository/CategorieRepository.php
public function findWithAvailableProducts(Espace $espace): array
{
    return $this->createQueryBuilder('c')
        ->leftJoin('c.produits', 'p')
        ->andWhere('c.espace = :espace')
        ->andWhere('p.estDisponible = true')
        ->setParameter('espace', $espace)
        ->groupBy('c.id')
        ->having('COUNT(p.id) > 0')
        ->getQuery()
        ->getResult();
}
    }
    //    /**
    //     * @return Categorie[] Returns an array of Categorie objects
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

    //    public function findOneBySomeField($value): ?Categorie
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

