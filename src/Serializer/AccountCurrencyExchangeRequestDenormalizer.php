<?php

namespace App\Serializer;

use App\Dto\Request\AccountCurrencyExchangeRequest;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AccountCurrencyExchangeRequestDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): AccountCurrencyExchangeRequest
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Expected an array');
        }

        return new AccountCurrencyExchangeRequest(
            fromAccountId: $data['fromAccountId'] ?? null,
            toAccountId: $data['toAccountId'] ?? null,
            amount: $data['amount'] ?? null
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === AccountCurrencyExchangeRequest::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [AccountCurrencyExchangeRequest::class => true];
    }
}
