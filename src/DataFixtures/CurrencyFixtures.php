<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $chf = new Currency();
        $chf->setCode('CHF');
        $chf->setName('Swiss Franc');
        $chf->setScale(100);
        $manager->persist($chf);

        $usd = new Currency();
        $usd->setCode('USD');
        $usd->setName('United States Dollar');
        $usd->setScale(100);
        $manager->persist($usd);

        $eur = new Currency();
        $eur->setCode('EUR');
        $eur->setName('Euro');
        $eur->setScale(100);
        $manager->persist($eur);

        $manager->flush();
    }
}
