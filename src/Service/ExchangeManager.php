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

            // Create Exchange record
            $exchange = new Exchange();
            $exchange->setFromCurrency($fromAccount->getCurrency());
            $exchange->setToCurrency($toAccount->getCurrency());
            $exchange->setFromAmount($fromAmountMinor);
            $exchange->setToAmount($toAmountMinor);
            $exchange->setExchangeRate($exchangeRate->getRate());

            // Create EXCHANGE transaction on source account
            $payoutTransaction = new Transaction();
            $payoutTransaction->setBusinessPartner($fromAccount->getBusinessPartner());
            $payoutTransaction->setAccount($fromAccount);
            $payoutTransaction->setExchange($exchange);
            $payoutTransaction->setType(TransactionTypeEnum::EXCHANGE);
            $payoutTransaction->setAmount('-' . ($fromAmountMinor / $fromAccount->getCurrencyScale()));
            $payoutTransaction->setName("Exchange: {$fromAccount->getCurrencyCode()} → {$toAccount->getCurrencyCode()}");
            $payoutTransaction->setDate(new DateTimeImmutable());
            $payoutTransaction->setCountry($fromAccount->getBusinessPartner()->getCountry());
            $payoutTransaction->setIban('');
            $payoutTransaction->setExecuted(true);

            // Create EXCHANGE transaction on destination account
            $payinTransaction = new Transaction();
            $payinTransaction->setBusinessPartner($toAccount->getBusinessPartner());
            $payinTransaction->setAccount($toAccount);
            $payinTransaction->setExchange($exchange);
            $payinTransaction->setType(TransactionTypeEnum::EXCHANGE);
            $payinTransaction->setAmount('+' . $toAmountMinor / $toAccount->getCurrencyScale());
            $payinTransaction->setName("Exchange: {$fromAccount->getCurrencyCode()} → {$toAccount->getCurrencyCode()}");
            $payinTransaction->setDate(new DateTimeImmutable());
            $payinTransaction->setCountry($toAccount->getBusinessPartner()->getCountry());
            $payinTransaction->setIban($toAccount->getAccountNumber());
            $payinTransaction->setExecuted(true);

            // Update account balances
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
}
