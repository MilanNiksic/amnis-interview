<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\BusinessPartner;
use App\Enums\BusinessPartnerStatusEnum;
use App\Enums\LegalFormEnum;
use App\Service\BalanceManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BalanceManagerTest extends WebTestCase
{
    private BalanceManager $balanceManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $container = static::getContainer();

        $this->balanceManager = $container->get(BalanceManager::class);
    }

    public function testPayinBalanceChange(): void
    {
        $businessPartner = $this->createBusinessPartner();

        $this->balanceManager->increaseBalance($businessPartner, '1000');

        $this->assertEquals('11000', $businessPartner->getBalance());
    }

    public function testPayoutBalanceChange(): void
    {
        $businessPartner = $this->createBusinessPartner();

        $this->balanceManager->decreaseBalance($businessPartner, '1000');

        $this->assertEquals('9000', $businessPartner->getBalance());
    }

    public function testHasEnoughMoneyForPayout(): void
    {
        $businessPartner = $this->createBusinessPartner();

        $this->assertTrue($this->balanceManager->hasEnoughMoneyForPayout($businessPartner, '1000'));
        $this->assertFalse($this->balanceManager->hasEnoughMoneyForPayout($businessPartner, '11000'));
    }

    private function createBusinessPartner(): BusinessPartner
    {
        $businessPartner = new BusinessPartner();
        $businessPartner->setName('AMNIS Treasury Services AG');
        $businessPartner->setStatus(BusinessPartnerStatusEnum::ACTIVE);
        $businessPartner->setLegalForm(LegalFormEnum::LIMITED_LIABILITY_COMPANY);
        $businessPartner->setBalance('10000');
        $businessPartner->setAddress('Baslerstrasse 60');
        $businessPartner->setCity('Zürich');
        $businessPartner->setZip('8048');
        $businessPartner->setCountry('CH');

        return $businessPartner;
    }
}
