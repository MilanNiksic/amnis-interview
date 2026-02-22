<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AccountFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $partner1 = $this->getReference(BusinessPartnerFixtures::class . '::partnerTreasury');
        $partner2 = $this->getReference(BusinessPartnerFixtures::class . '::partnerAmnis');
        $chf = $this->getReference(CurrencyFixtures::class . '::CHF');

        // Account for AMNIS Treasury Services AG - 10,000 CHF
        $account1 = new Account();
        $account1->setBusinessPartner($partner1);
        $account1->setCurrency($chf);
        $account1->setName('Main CHF Account - Treasury');
        $account1->setAccountNumber('CH93-0076-2011-6238-5295-7');
        $account1->setBalanceMinor(1000000); // 10,000 CHF * 100 scale
        $account1->setIsActive(true);
        $manager->persist($account1);

        // Account for AMNIS Europe AG - 500 CHF
        $account2 = new Account();
        $account2->setBusinessPartner($partner2);
        $account2->setCurrency($chf);
        $account2->setName('Main CHF Account - Europe');
        $account2->setAccountNumber('CH56-0483-5012-3456-7100-0');
        $account2->setBalanceMinor(50000); // 500 CHF * 100 scale
        $account2->setIsActive(true);
        $manager->persist($account2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            BusinessPartnerFixtures::class,
            CurrencyFixtures::class,
        ];
    }
}
