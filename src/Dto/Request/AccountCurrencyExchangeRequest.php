<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class AccountCurrencyExchangeRequest
{
    #[Assert\NotNull(message: 'From Account ID is required')]
    #[Assert\Type(type: 'int', message: 'From Account ID must be an integer')]
    #[Assert\Positive(message: 'From Account ID must be positive')]
    public ?int $fromAccountId = null;

    #[Assert\NotNull(message: 'To Account ID is required')]
    #[Assert\Type(type: 'int', message: 'To Account ID must be an integer')]
    #[Assert\Positive(message: 'To Account ID must be positive')]
    public ?int $toAccountId = null;

    #[Assert\NotNull(message: 'Amount is required')]
    #[Assert\Type(type: 'numeric', message: 'Amount must be a numeric value')]
    #[Assert\Positive(message: 'Amount must be greater than 0')]
    public mixed $amount = null;

    public function __construct(?int $fromAccountId = null, ?int $toAccountId = null, mixed $amount = null)
    {
        $this->fromAccountId = $fromAccountId;
        $this->toAccountId = $toAccountId;
        $this->amount = $amount;
    }
}
