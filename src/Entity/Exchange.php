<?php

namespace App\Entity;

use App\Repository\ExchangeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExchangeRepository::class)]
#[ORM\Table(name: 'exchanges')]
#[ORM\HasLifecycleCallbacks]
class Exchange
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false)]
    private Currency $fromCurrency;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false)]
    private Currency $toCurrency;

    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Type('int')]
    private int $fromAmount = 0;

    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Type('int')]
    private int $toAmount = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 8)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private float $exchangeRate;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
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

    public function getFromAmount(): int
    {
        return $this->fromAmount;
    }

    public function setFromAmount(int $fromAmount): void
    {
        $this->fromAmount = $fromAmount;
    }

    public function getToAmount(): int
    {
        return $this->toAmount;
    }

    public function setToAmount(int $toAmount): void
    {
        $this->toAmount = $toAmount;
    }

    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(float $exchangeRate): void
    {
        $this->exchangeRate = $exchangeRate;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
