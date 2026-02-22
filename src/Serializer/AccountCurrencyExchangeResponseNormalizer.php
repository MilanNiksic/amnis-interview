<?php

namespace App\Serializer;

use App\Dto\Response\AccountCurrencyExchangeResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AccountCurrencyExchangeResponseNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!$object instanceof AccountCurrencyExchangeResponse) {
            throw new \InvalidArgumentException('Expected AccountCurrencyExchangeResponse');
        }

        return [
            'id' => $object->id,
            'fromAccountId' => $object->fromAccountId,
            'toAccountId' => $object->toAccountId,
            'fromCurrency' => $object->fromCurrency,
            'toCurrency' => $object->toCurrency,
            'fromAmount' => $object->fromAmount,
            'toAmount' => $object->toAmount,
            'exchangeRate' => $object->exchangeRate,
            'createdAt' => $object->createdAt,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AccountCurrencyExchangeResponse;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [AccountCurrencyExchangeResponse::class => true];
    }
}
