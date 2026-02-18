<?php

namespace App\Controller\Api;

use App\Entity\Transaction;
use App\Enums\TransactionTypeEnum;
use App\Service\PayinManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PayinController extends AbstractController
{
    public function __construct(protected readonly PayinManager $payinManager)
    {
    }

    public function __invoke(Transaction $transaction)
    {
        $transaction->setType(TransactionTypeEnum::PAYIN);
        
        $this->payinManager->execute($transaction);

        return $transaction;
    }
}