<?php

namespace App\Service;

use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;

class BalanceManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function increaseBalance(Account $account, string $amount): string
    {
        $balance = (float)$account->getBalance();
        $balance += (float)$amount;
        $account->setBalance($balance);
        $this->entityManager->flush();

        return $balance;
    }

    public function decreaseBalance(Account $account, string $amount): string
    {
        $balance = (float)$account->getBalance();
        $balance -= (float)$amount;
        $account->setBalance($balance);
        $this->entityManager->flush();

        return $balance;
    }

    public function hasEnoughMoneyForPayout(Account $account, string $amount): bool
    {
        $remainingBalance = (float)$account->getBalance();
        $remainingBalance -= (float)$amount;

        return $remainingBalance >= 0;
    }
}