<?php

namespace App\Controller\Api;

use App\Entity\Transaction;
use App\Service\PayoutManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PayoutExecutionController extends AbstractController
{
    public function __construct(private readonly PayoutManager $payoutManager)
    {
    }

    public function __invoke(Transaction $transaction)
    {
        $this->payoutManager->execute($transaction);

        return $transaction;
    }
}