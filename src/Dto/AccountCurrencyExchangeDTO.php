<?php

namespace App\Dto;

use App\Entity\Account;
use App\Entity\BusinessPartner;
use Symfony\Component\Validator\Constraints as Assert;

class AccountCurrencyExchangeDTO
{
    #[Assert\NotBlank(['message' => 'Business Partner is required'])]
    public ?BusinessPartner $businessPartner = null;

    #[Assert\NotBlank(['message' => 'From Account is required'])]
    public ?Account $fromAccount = null;

    #[Assert\NotBlank(['message' => 'To Account is required'])]
    public ?Account $toAccount = null;

    #[Assert\NotBlank(['message' => 'Amount is required'])]
    #[Assert\Positive(['message' => 'Amount must be greater than 0'])]
    public ?float $amount = null;
}
