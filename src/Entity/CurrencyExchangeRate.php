<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Repository\CurrencyExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyExchangeRateRepository::class)]
#[ORM\Table(name: 'currency_exchange_rates')]
#[ApiResource(
    operations: [
        new Get(),
        new Get(
            uriTemplate: '/currencies/{fromCurrency}/rate/{toCurrency}',
            controller: \App\Controller\Api\ExchangeRateBetweenCurrenciesController::class,
            name: 'get_exchange_rate_between_currencies'
        )
    ],
    normalizationContext: ['groups' => ['CurrencyExchangeRateView']]
)]
class CurrencyExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['CurrencyExchangeRateView'])]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'exchangeRatesFrom')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['CurrencyExchangeRateView'])]
    private Currency $fromCurrency;

    #[ORM\ManyToOne(inversedBy: 'exchangeRatesTo')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['CurrencyExchangeRateView'])]
    private Currency $toCurrency;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 8)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Groups(['CurrencyExchangeRateView'])]
    private string $rate;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['CurrencyExchangeRateView'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromCurrency(): Currency
    {
        return $this->fromCurrency;
    }

    public function setFromCurrency(Currency $fromCurrency): void
    {
        $this->fromCurrency = $fromCurrency;
    }

    public function getToCurrency(): Currency
    {
        return $this->toCurrency;
    }

    public function setToCurrency(Currency $toCurrency): void
    {
        $this->toCurrency = $toCurrency;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function setRate(string $rate): void
    {
        $this->rate = $rate;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

