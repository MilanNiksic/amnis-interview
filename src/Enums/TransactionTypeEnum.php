<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case PAYIN = 'payin';
    case PAYOUT = 'payout';
    case EXCHANGE = 'exchange';
}
