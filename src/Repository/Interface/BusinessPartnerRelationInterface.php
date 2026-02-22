<?php

namespace App\Repository\Interface;

use App\Entity\BusinessPartner;

interface BusinessPartnerRelationInterface
{
    public function findByBusinessPartner(BusinessPartner $businessPartner): array;
}
