<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Currency;
use App\Entity\CurrencyExchangeRate;
use App\Entity\Exchange;
use App\Entity\Transaction;
use App\Enums\TransactionTypeEnum;
use App\Repository\CurrencyExchangeRateRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class ExchangeManager
{
    public function __construct(private EntityManagerInterface $entityManager, private CurrencyExchangeRateRepository $exchangeRateRepository)
    {
    }

    public function validateExchangeData(
        Account $fromAccount,
        Account $toAccount,
        float $amount
    ): void {
        // Validate accounts are different
        if ($fromAccount->getId() === $toAccount->getId()) {
            throw new \InvalidArgumentException('From and To accounts must be different');
        }

        // Validate same business partner
        if ($fromAccount->getBusinessPartner()->getId() !== $toAccount->getBusinessPartner()->getId()) {
            throw new \InvalidArgumentException('Both accounts must belong to the same business partner');
        }

        // Validate currencies are different
        if ($fromAccount->getCurrency()->getId() === $toAccount->getCurrency()->getId()) {
            throw new \InvalidArgumentException('From and To accounts must have different currencies');
        }

        // Validate sufficient balance
        if ($fromAccount->getBalance() < $amount) {
            throw new \InvalidArgumentException('Insufficient balance in source account');
        }
    }

    public function executeExchange(
        Account $fromAccount,
        Account $toAccount,
        float $fromAmount
    ): Exchange {
        $this->entityManager->beginTransaction();

        try {
            // Re-fetch accounts with current data to ensure we have the latest balance
            $fromAccount = $this->entityManager->find(Account::class, $fromAccount->getId(), LockMode::PESSIMISTIC_WRITE);
            $toAccount = $this->entityManager->find(Account::class, $toAccount->getId(), LockMode::PESSIMISTIC_WRITE);

            $this->validateExchangeData($fromAccount, $toAccount, $fromAmount);

            $exchangeRate = $this->getExchangeRate($fromAccount->getCurrency(), $toAccount->getCurrency());

            $fromAmountMinor = (int)round($fromAmount * $fromAccount->getCurrencyScale(), PHP_ROUND_HALF_UP);
            $toAmountMinor = (int)round($fromAmountMinor * $exchangeRate->getRate(), PHP_ROUND_HALF_UP);

            $exchange = $this->createExchange($fromAccount, $toAccount, $fromAmountMinor, $toAmountMinor, $exchangeRate);
            $payoutTransaction = $this->createPayoutTransaction($fromAccount, $toAccount, $exchange, $fromAmountMinor);
            $payinTransaction = $this->createPayinTransaction($fromAccount, $toAccount, $exchange, $toAmountMinor);

            $fromAccount->setBalanceMinor($fromAccount->getBalanceMinor() - $fromAmountMinor);
            $toAccount->setBalanceMinor($toAccount->getBalanceMinor() + $toAmountMinor);

            $this->entityManager->persist($exchange);
            $this->entityManager->persist($payoutTransaction);
            $this->entityManager->persist($payinTransaction);
            $this->entityManager->persist($fromAccount);
            $this->entityManager->persist($toAccount);
            $this->entityManager->flush();

            $this->entityManager->commit();

            return $exchange;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    private function getExchangeRate(Currency $fromCurrency, Currency $toCurrency): CurrencyExchangeRate
    {
        $exchangeRate = $this->exchangeRateRepository->findOneBy(
            [
                'fromCurrency' => $fromCurrency,
                'toCurrency' => $toCurrency
            ]
        );

        if (!$exchangeRate) {
            throw new \RuntimeException('Exchange rate not found for the given currency pair');
        }

        return $exchangeRate;
    }

    private function createExchange(
        Account $fromAccount,
        Account $toAccount,
        int $fromAmountMinor,
        int $toAmountMinor,
        CurrencyExchangeRate $exchangeRate
    ): Exchange {
        $exchange = new Exchange();
        $exchange->setFromCurrency($fromAccount->getCurrency());
        $exchange->setToCurrency($toAccount->getCurrency());
        $exchange->setFromAmount($fromAmountMinor);
        $exchange->setToAmount($toAmountMinor);
        $exchange->setExchangeRate($exchangeRate->getRate());

        return $exchange;
    }

    private function createPayoutTransaction(
        Account $fromAccount,
        Account $toAccount,
        Exchange $exchange,
        int $fromAmountMinor
    ): Transaction {
        $transaction = new Transaction();
        $transaction->setBusinessPartner($fromAccount->getBusinessPartner());
        $transaction->setAccount($fromAccount);
        $transaction->setExchange($exchange);
        $transaction->setType(TransactionTypeEnum::EXCHANGE);
        $transaction->setAmount('-' . ($fromAmountMinor / $fromAccount->getCurrencyScale()));
        $transaction->setName("Exchange: {$fromAccount->getCurrencyCode()} → {$toAccount->getCurrencyCode()}");
        $transaction->setDate(new DateTimeImmutable());
        $transaction->setCountry($fromAccount->getBusinessPartner()->getCountry());
        $transaction->setIban('');
        $transaction->setExecuted(true);

        return $transaction;
    }

    private function createPayinTransaction(
        Account $fromAccount,
        Account $toAccount,
        Exchange $exchange,
        int $toAmountMinor
    ): Transaction {
        $transaction = new Transaction();
        $transaction->setBusinessPartner($toAccount->getBusinessPartner());
        $transaction->setAccount($toAccount);
        $transaction->setExchange($exchange);
        $transaction->setType(TransactionTypeEnum::EXCHANGE);
        $transaction->setAmount('+' . $toAmountMinor / $toAccount->getCurrencyScale());
        $transaction->setName("Exchange: {$fromAccount->getCurrencyCode()} → {$toAccount->getCurrencyCode()}");
        $transaction->setDate(new DateTimeImmutable());
        $transaction->setCountry($toAccount->getBusinessPartner()->getCountry());
        $transaction->setIban($toAccount->getAccountNumber());
        $transaction->setExecuted(true);

        return $transaction;
    }
}
