<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ORM\Table(name: 'currencies')]
#[ApiResource(
    operations: [
        new Get(),
        new Post(
            denormalizationContext: ['groups' => ['CurrencyCreate']]
        ),
        new Patch(
            uriTemplate: '/currencies/{id}/activate',
            denormalizationContext: ['groups' => ['CurrencyPatch']]
        ),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['CurrencyView']]
)]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['CurrencyView'])]
    private int $id;

    #[ORM\Column(length: 3, unique: true)]
    #[Assert\Length(min: 3, max: 3)]
    #[Groups(['CurrencyView', 'CurrencyCreate'])]
    private string $code;

    #[ORM\Column(length: 255)]
    #[Groups(['CurrencyView', 'CurrencyCreate'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive(message: 'Scale must be a positive number larger than 0')]
    #[Assert\Length(min: 1, max: 4)]
    #[Groups(['CurrencyCreate'])]
    private int $scale;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    #[Groups(['CurrencyView', 'CurrencyPatch'])]
    private bool $isActive = true;

    #[ORM\OneToMany(targetEntity: CurrencyExchangeRate::class, mappedBy: 'fromCurrency')]
    private Collection $exchangeRatesFrom;

    #[ORM\OneToMany(targetEntity: CurrencyExchangeRate::class, mappedBy: 'toCurrency')]
    private Collection $exchangeRatesTo;

    public function __construct()
    {
        $this->exchangeRatesFrom = new ArrayCollection();
        $this->exchangeRatesTo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getScale(): int
    {
        return $this->scale;
    }

    public function setScale(int $scale): void
    {
        $this->scale = $scale;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return Collection<int, CurrencyExchangeRate>
     */
    public function getExchangeRatesFrom(): Collection
    {
        return $this->exchangeRatesFrom;
    }

    public function addExchangeRateFrom(CurrencyExchangeRate $exchangeRate): void
    {
        if (!$this->exchangeRatesFrom->contains($exchangeRate)) {
            $this->exchangeRatesFrom->add($exchangeRate);
            $exchangeRate->setFromCurrency($this);
        }
    }

    public function removeExchangeRateFrom(CurrencyExchangeRate $exchangeRate): void
    {
        $this->exchangeRatesFrom->removeElement($exchangeRate);
    }

    /**
     * @return Collection<int, CurrencyExchangeRate>
     */
    public function getExchangeRatesTo(): Collection
    {
        return $this->exchangeRatesTo;
    }

    public function addExchangeRateTo(CurrencyExchangeRate $exchangeRate): void
    {
        if (!$this->exchangeRatesTo->contains($exchangeRate)) {
            $this->exchangeRatesTo->add($exchangeRate);
            $exchangeRate->setToCurrency($this);
        }
    }

    public function removeExchangeRateTo(CurrencyExchangeRate $exchangeRate): void
    {
        $this->exchangeRatesTo->removeElement($exchangeRate);
    }
}
