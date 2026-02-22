<?php

namespace App\Dto\Response;

class AccountCurrencyExchangeResponse
{
    public function __construct(
        public int $id,
        public int $fromAccountId,
        public int $toAccountId,
        public string $fromCurrency,
        public string $toCurrency,
        public float $fromAmount,
        public float $toAmount,
        public float $exchangeRate,
        public string $createdAt
    ) {
    }
}
