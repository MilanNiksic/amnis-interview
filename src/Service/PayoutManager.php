<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Enums\TransactionTypeEnum;
use App\Exceptions\TransactionExecutionException;
use DateTime;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

class PayoutManager
{
    public function __construct(
        private readonly BalanceManager $balanceManager,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function execute(Transaction $transaction): void
    {
        $this->entityManager->beginTransaction();

        try {
            $businessPartner = $transaction->getBusinessPartner();
            $account = $transaction->getAccount();

            $account = $this->entityManager->find(Account::class, $account->getId(), LockMode::PESSIMISTIC_WRITE);

            if (!$businessPartner->getAccounts()->contains($account)) {
                throw new TransactionExecutionException('Account does not belong to business partner!');
            }

            if ($transaction->getType() !== TransactionTypeEnum::PAYOUT) {
                throw new TransactionExecutionException('Transaction type is not payout');
            }

            if ($transaction->isExecuted()) {
                throw new TransactionExecutionException('Transaction is already executed');
            }

            if ($transaction->getDate() > (new DateTime())) {
                throw new TransactionExecutionException('Payout transaction date can be only on the current date');
            }

            if (!$this->balanceManager->hasEnoughMoneyForPayout(
                $account,
                $transaction->getAmount()
            )) {
                throw new TransactionExecutionException('You do not have enough money for a payout');
            }

            $transaction->setExecuted(true);
            $this->balanceManager->decreaseBalance($account, $transaction->getAmount());

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}