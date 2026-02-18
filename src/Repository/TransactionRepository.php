<?php

namespace App\Repository;

use App\Entity\BusinessPartner;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findByBusinessPartner(BusinessPartner $businessPartner): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.businessPartner = :businessPartner')
            ->setParameter('businessPartner', $businessPartner)
            ->getQuery()
            ->getResult();
    }
}
