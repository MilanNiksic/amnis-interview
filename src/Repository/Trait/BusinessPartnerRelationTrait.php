<?php

namespace App\Repository\Trait;

use App\Entity\BusinessPartner;

trait BusinessPartnerRelationTrait
{
    public function findByBusinessPartner(BusinessPartner $businessPartner): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.businessPartner = :businessPartner')
            ->setParameter('businessPartner', $businessPartner)
            ->getQuery()
            ->getResult();
    }
}
