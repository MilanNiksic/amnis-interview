<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Repository\Interface\BusinessPartnerRelationInterface;
use App\Repository\Trait\BusinessPartnerRelationTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository implements BusinessPartnerRelationInterface
{
    use BusinessPartnerRelationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findByAccount(Account $account): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.account = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->getResult();
    }
}
