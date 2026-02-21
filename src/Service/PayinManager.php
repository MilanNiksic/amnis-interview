<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Enums\TransactionTypeEnum;
use App\Exceptions\TransactionExecutionException;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

class PayinManager
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
            if ($transaction->getType() !== TransactionTypeEnum::PAYIN) {
                throw new TransactionExecutionException('Transaction type is not payin');
            }

            if ($transaction->isExecuted()) {
                throw new TransactionExecutionException('Transaction is already executed');
            }

            $account = $transaction->getAccount();
            $businessPartner = $transaction->getBusinessPartner();

            $account = $this->entityManager->find(Account::class, $account->getId(), LockMode::PESSIMISTIC_WRITE);

            if (!$businessPartner->getAccounts()->contains($account)) {
                throw new TransactionExecutionException('Account does not belong to business partner!');
            }

            $transaction->setExecuted(true);
            $this->balanceManager->increaseBalance($account, $transaction->getAmount());

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}