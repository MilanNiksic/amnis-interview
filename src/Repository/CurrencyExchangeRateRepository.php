<?php

namespace App\Repository;

use App\Entity\CurrencyExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CurrencyExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyExchangeRate::class);
    }
}
