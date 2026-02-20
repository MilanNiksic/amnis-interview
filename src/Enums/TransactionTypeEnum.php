<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case PAYIN = 'payin';
    case PAYOUT = 'payout';
    case EXCHANGE_PAYIN = 'exchange_payin';
    case EXCHANGE_PAYOUT = 'exchange_payout';
}
