<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\Account;
use App\Entity\BusinessPartner;
use App\Entity\Currency;
use App\Entity\CurrencyExchangeRate;
use App\Entity\Exchange;
use App\Entity\Transaction;
use App\Enums\BusinessPartnerStatusEnum;
use App\Enums\LegalFormEnum;
use App\Enums\TransactionTypeEnum;
use App\Service\ExchangeManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Repository\CurrencyExchangeRateRepository;

/**
 * Pure unit tests for ExchangeManager with mocked dependencies.
 * No database access - tests business logic in isolation.
 */
class ExchangeManagerUnitTest extends TestCase
{
    private ExchangeManager $exchangeManager;
    private EntityManagerInterface&MockObject $entityManager;
    private CurrencyExchangeRateRepository&MockObject $exchangeRateRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->exchangeRateRepository = $this->createMock(CurrencyExchangeRateRepository::class);

        $this->exchangeManager = new ExchangeManager(
            $this->entityManager,
            $this->exchangeRateRepository
        );
    }

    public function testValidateExchangeDataWithSameAccountThrowsException(): void
    {
        $account = $this->createStubAccount(1, 1, 'CHF');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('From and To accounts must be different');

        $this->exchangeManager->validateExchangeData($account, $account, 100);
    }

    public function testValidateExchangeDataWithDifferentBusinessPartnerThrowsException(): void
    {
        $account1 = $this->createStubAccount(1, 1, 'CHF');
        $account2 = $this->createStubAccount(2, 2, 'EUR');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Both accounts must belong to the same business partner');

        $this->exchangeManager->validateExchangeData($account1, $account2, 100);
    }

    public function testValidateExchangeDataWithSameCurrencyThrowsException(): void
    {
        $account1 = $this->createStubAccount(1, 1, 'CHF', 'CHF', 1000);
        $account2 = $this->createStubAccount(2, 1, 'CHF', 'CHF', 1000);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('From and To accounts must have different currencies');

        $this->exchangeManager->validateExchangeData($account1, $account2, 100);
    }

    public function testValidateExchangeDataWithInsufficientBalanceThrowsException(): void
    {
        $account1 = $this->createStubAccount(1, 1, 'CHF_1', 'CHF', 100); // 100 CHF
        $account2 = $this->createStubAccount(2, 1, 'EUR_1', 'EUR', 1000);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient balance in source account');

        $this->exchangeManager->validateExchangeData($account1, $account2, 200);
    }

    public function testValidateExchangeDataWithValidDataDoesNotThrow(): void
    {
        $account1 = $this->createStubAccount(1, 1, 'CHF_2', 'CHF', 1000);
        $account2 = $this->createStubAccount(2, 1, 'EUR_2', 'EUR', 0);

        // Should not throw
        $this->exchangeManager->validateExchangeData($account1, $account2, 500);
        $this->assertTrue(true);
    }

    public function testExecuteExchangeSuccessfully(): void
    {
        $account1 = $this->createStubAccount(1, 1, 'CHF_3', 'CHF', 1000);
        $account2 = $this->createStubAccount(2, 1, 'EUR_3', 'EUR', 0);

        $exchangeRate = $this->createStubExchangeRate(1.1);

        // Mock repository to return exchange rate
        $this->exchangeRateRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($exchangeRate);

        // Mock EntityManager methods
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('find')
            ->willReturnOnConsecutiveCalls($account1, $account2);

        $this->entityManager
            ->expects($this->once())
            ->method('beginTransaction');

        $this->entityManager
            ->expects($this->once())
            ->method('commit');

        $this->entityManager
            ->expects($this->exactly(5))
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $exchange = $this->exchangeManager->executeExchange($account1, $account2, 1000);

        $this->assertNotNull($exchange);
    }

    public function testExecuteExchangeUpdatesAccountBalances(): void
    {
        $account1 = $this->createStubAccount(1, 1, 'CHF_4', 'CHF', 1000);
        $account2 = $this->createStubAccount(2, 1, 'EUR_4', 'EUR', 0);

        $exchangeRate = $this->createStubExchangeRate(1.1);

        $this->exchangeRateRepository
            ->method('findOneBy')
            ->willReturn($exchangeRate);

        $this->entityManager
            ->method('find')
            ->willReturnOnConsecutiveCalls($account1, $account2);

        $this->entityManager->method('beginTransaction');
        $this->entityManager->method('commit');
        $this->entityManager->method('persist');
        $this->entityManager->method('flush');

        // Mock setBalance to verify it was called with correct values
        $account1->expects($this->once())->method('setBalanceMinor')->with(0);
        $account2->expects($this->once())->method('setBalanceMinor')->with(110000);

        $this->exchangeManager->executeExchange($account1, $account2, 1000);
    }

    public function testExecuteExchangeRollsBackOnException(): void
    {
        $account1 = $this->createStubAccount(1, 1, 'CHF_5', 'CHF', 1000);
        $account2 = $this->createStubAccount(2, 1, 'EUR_5', 'EUR', 0);

        // Mock repository to throw exception (missing exchange rate)
        $this->exchangeRateRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willThrowException(new \RuntimeException('Exchange rate not found for the given currency pair'));

        $this->entityManager
            ->method('find')
            ->willReturnOnConsecutiveCalls($account1, $account2);

        $this->entityManager
            ->expects($this->once())
            ->method('beginTransaction');

        $this->entityManager
            ->expects($this->once())
            ->method('rollback');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Exchange rate not found for the given currency pair');
        $this->exchangeManager->executeExchange($account1, $account2, 1000);
    }

    public function testExecuteExchangeCallsRepositoryWithCorrectCurrencies(): void
    {
        $account1 = $this->createStubAccount(1, 1, 'CHF_6', 'CHF', 1000);
        $account2 = $this->createStubAccount(2, 1, 'EUR_6', 'EUR', 0);

        $exchangeRate = $this->createStubExchangeRate(1.1);

        // Verify repository is called with correct currency pair
        $this->exchangeRateRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->callback(function ($arg) use ($account1, $account2) {
                return $arg['fromCurrency'] === $account1->getCurrency() &&
                       $arg['toCurrency'] === $account2->getCurrency();
            }))
            ->willReturn($exchangeRate);

        $this->entityManager->method('find')->willReturnOnConsecutiveCalls($account1, $account2);
        $this->entityManager->method('beginTransaction');
        $this->entityManager->method('commit');
        $this->entityManager->method('persist');
        $this->entityManager->method('flush');

        $this->exchangeManager->executeExchange($account1, $account2, 1000);
        $this->assertTrue(true);
    }

    private function createStubAccount(
        int $id,
        int $businessPartnerId,
        string $currencyCode,
        string $currencyName = 'CHF',
        int $balance = 0
    ): Account {
        $currency = $this->createMock(Currency::class);
        $currency->method('getId')->willReturn(crc32($currencyCode));
        $currency->method('getCode')->willReturn(substr($currencyCode, 0, 3));
        $currency->method('getScale')->willReturn(100);

        $businessPartner = $this->createMock(BusinessPartner::class);
        $businessPartner->method('getId')->willReturn($businessPartnerId);

        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn($id);
        $account->method('getCurrency')->willReturn($currency);
        $account->method('getBusinessPartner')->willReturn($businessPartner);
        $account->method('getBalance')->willReturn((float)$balance);
        $account->method('getBalanceMinor')->willReturn($balance * 100);
        $account->method('getCurrencyScale')->willReturn(100);
        $account->method('getAccountNumber')->willReturn("ACC{$id}");

        return $account;
    }

    private function createStubExchangeRate(float $rate): CurrencyExchangeRate
    {
        $exchangeRate = $this->createMock(CurrencyExchangeRate::class);
        $exchangeRate->method('getRate')->willReturn((string)$rate);

        return $exchangeRate;
    }
}

