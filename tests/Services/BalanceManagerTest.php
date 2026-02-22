<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\Account;
use App\Entity\BusinessPartner;
use App\Entity\Currency;
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
        $account = $this->createAccount();

        $this->balanceManager->increaseBalance($account, '1000');

        $this->assertEquals(11000.0, $account->getBalance());
    }

    public function testPayoutBalanceChange(): void
    {
        $account = $this->createAccount();

        $this->balanceManager->decreaseBalance($account, '1000');

        $this->assertEquals(9000.0, $account->getBalance());
    }

    public function testHasEnoughMoneyForPayout(): void
    {
        $account = $this->createAccount();

        $this->assertTrue($this->balanceManager->hasEnoughMoneyForPayout($account, '1000'));
        $this->assertFalse($this->balanceManager->hasEnoughMoneyForPayout($account, '11000'));
    }

    private function createAccount(): Account
    {
        $businessPartner = new BusinessPartner();
        $businessPartner->setName('AMNIS Treasury Services AG');
        $businessPartner->setStatus(BusinessPartnerStatusEnum::ACTIVE);
        $businessPartner->setLegalForm(LegalFormEnum::LIMITED_LIABILITY_COMPANY);
        $businessPartner->setAddress('Baslerstrasse 60');
        $businessPartner->setCity('Zürich');
        $businessPartner->setZip('8048');
        $businessPartner->setCountry('CH');

        $currency = new Currency();
        $currency->setCode('CHF');
        $currency->setScale(100);
        $currency->setIsActive(true);

        $account = new Account();
        $account->setName('Main Account');
        $account->setAccountNumber('CH9300762011623852957');
        $account->setBusinessPartner($businessPartner);
        $account->setCurrency($currency);
        $account->setBalance(10000.0); // 10000.00 CHF = 1000000 balanceMinor

        return $account;
    }
}
