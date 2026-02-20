<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Enums\TransactionTypeEnum;
use App\Exceptions\TransactionExecutionException;

class PayinManager
{
    public function __construct(private readonly BalanceManager $balanceManager)
    {
    }

    public function execute(Transaction $transaction): void
    {
        if ($transaction->getType() !== TransactionTypeEnum::PAYIN) {
            throw new TransactionExecutionException('Transaction type is not payin');
        }

        if ($transaction->isExecuted()) {
            throw new TransactionExecutionException('Transaction is already executed');
        }

        $account = $transaction->getAccount();
        $businessPartner = $transaction->getBusinessPartner();
        if (!$businessPartner->getAccounts()->contains($account)) {
            throw new TransactionExecutionException('Account does not belong to business partner!');
        }

        $transaction->setExecuted(true);

        $this->balanceManager->increaseBalance($account, $transaction->getAmount());
    }
}