<?php

namespace App\Service;

use App\Entity\BusinessPartner;
use Doctrine\ORM\EntityManagerInterface;

class BalanceManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function increaseBalance(BusinessPartner $businessPartner, string $amount): string
    {
        $balance = (float)$businessPartner->getBalance();
        $balance += (float)$amount;
        $businessPartner->setBalance((string)$balance);

        $this->entityManager->flush();

        return $balance;
    }

    public function decreaseBalance(BusinessPartner $businessPartner, string $amount): string
    {
        $balance = (float)$businessPartner->getBalance();
        $balance -= (float)$amount;
        $businessPartner->setBalance((string)$balance);

        $this->entityManager->flush();

        return $balance;
    }

    public function hasEnoughMoneyForPayout(BusinessPartner $businessPartner, string $amount): bool
    {
        $remainingBalance = (float)$businessPartner->getBalance();
        $remainingBalance -= (float)$amount;

        return $remainingBalance >= 0;
    }
}