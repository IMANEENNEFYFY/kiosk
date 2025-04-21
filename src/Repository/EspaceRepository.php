<?php

// src/Repository/EspaceRepository.php
namespace App\Repository;

use App\Entity\Espace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EspaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Espace::class);
    }

    public function findOneByIdentifiant(string $identifiant): ?Espace
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.identifiant = :identifiant')
            ->setParameter('identifiant', $identifiant)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWithCategories(string $identifiant): ?Espace
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.categories', 'c')
            ->addSelect('c')
            ->andWhere('e.identifiant = :identifiant')
            ->setParameter('identifiant', $identifiant)
            ->getQuery()
            ->getOneOrNullResult();
    }
}