<?php

namespace App\DataFixtures;

use App\Entity\CurrencyExchangeRate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CurrencyExchangeRateFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $chf = $this->getReference(CurrencyFixtures::class . '::CHF');
        $usd = $this->getReference(CurrencyFixtures::class . '::USD');
        $eur = $this->getReference(CurrencyFixtures::class . '::EUR');

        // CHF to USD
        $chfToUsd = new CurrencyExchangeRate();
        $chfToUsd->setFromCurrency($chf);
        $chfToUsd->setToCurrency($usd);
        $chfToUsd->setRate('1.12');
        $manager->persist($chfToUsd);

        // CHF to EUR
        $chfToEur = new CurrencyExchangeRate();
        $chfToEur->setFromCurrency($chf);
        $chfToEur->setToCurrency($eur);
        $chfToEur->setRate('1.1');
        $manager->persist($chfToEur);

        // USD to CHF
        $usdToChf = new CurrencyExchangeRate();
        $usdToChf->setFromCurrency($usd);
        $usdToChf->setToCurrency($chf);
        $usdToChf->setRate('0.89');
        $manager->persist($usdToChf);

        // USD to EUR
        $usdToEur = new CurrencyExchangeRate();
        $usdToEur->setFromCurrency($usd);
        $usdToEur->setToCurrency($eur);
        $usdToEur->setRate('0.87');
        $manager->persist($usdToEur);

        // EUR to CHF
        $eurToChf = new CurrencyExchangeRate();
        $eurToChf->setFromCurrency($eur);
        $eurToChf->setToCurrency($chf);
        $eurToChf->setRate('0.987');
        $manager->persist($eurToChf);

        // EUR to USD - LEFT OUT for testing validation of missing exchange rate

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CurrencyFixtures::class,
        ];
    }
}
