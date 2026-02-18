<?php

namespace App\DataFixtures;

use App\Entity\BusinessPartner;
use App\Enums\BusinessPartnerStatusEnum;
use App\Enums\LegalFormEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BusinessPartnerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $businessPartner = new BusinessPartner();
        $businessPartner->setName('AMNIS Treasury Services AG');
        $businessPartner->setStatus(BusinessPartnerStatusEnum::ACTIVE);
        $businessPartner->setLegalForm(LegalFormEnum::LIMITED_LIABILITY_COMPANY);
        $businessPartner->setBalance('10000');
        $businessPartner->setAddress('Baslerstrasse 60');
        $businessPartner->setCity('Zürich');
        $businessPartner->setZip(8048);
        $businessPartner->setCountry('CH');

        $manager->persist($businessPartner);

        $businessPartner = new BusinessPartner();
        $businessPartner->setName('AMNIS Europe AG');
        $businessPartner->setStatus(BusinessPartnerStatusEnum::INACTIVE);
        $businessPartner->setLegalForm(LegalFormEnum::LIMITED_LIABILITY_COMPANY);
        $businessPartner->setBalance('10000');
        $businessPartner->setAddress('Gewerbeweg 15');
        $businessPartner->setCity('Vaduz');
        $businessPartner->setZip(9490);
        $businessPartner->setCountry('LI');

        $manager->persist($businessPartner);

        $manager->flush();
    }
}
