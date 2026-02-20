<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\Table(name: 'accounts', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'UNIQ_business_partner_currency', columns: ['business_partner_id', 'currency_id'])
])]
#[UniqueEntity(
    fields: ['businessPartner', 'currency'],
    message: 'This partner already has an account for this currency.'
)]
#[ApiResource(
    operations: [
        new Get(),
        new Post(
            denormalizationContext: ['groups' => ['AccountCreate']]
        ),
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/business_partners/{businessPartnerId}/accounts',
            uriVariables: [
                'businessPartnerId' => new Link(
                    fromClass: BusinessPartner::class,
                    fromProperty: 'accounts'
                )
            ],
            name: 'get_accounts_by_business_partner'
        ),
    ],
    normalizationContext: ['groups' => ['AccountView']]
)]

class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['AccountView'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: BusinessPartner::class, inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Groups(['AccountView', 'AccountCreate'])]
    private BusinessPartner $businessPartner;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Groups(['AccountView', 'AccountCreate'])]
    private Currency $currency;

    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    #[Assert\Type('int')]
    #[Groups(['AccountView', 'AccountCreate'])]
    private int $balanceMinor = 0;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups(['AccountView', 'AccountCreate'])]
    private string $name;

    #[ORM\Column(length: 50)]
    #[Assert\Length(min: 1, max: 50)]
    #[Groups(['AccountView', 'AccountCreate'])]
    private string $accountNumber;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    #[Groups(['AccountView'])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['AccountView'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['AccountView'])]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusinessPartner(): BusinessPartner
    {
        return $this->businessPartner;
    }

    public function setBusinessPartner(BusinessPartner $businessPartner): void
    {
        $this->businessPartner = $businessPartner;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }

    public function getBalanceMinor(): int
    {
        return $this->balanceMinor;
    }

    public function setBalanceMinor(int $balanceMinor): void
    {
        $this->balanceMinor = $balanceMinor;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    #[Groups(['AccountView'])]
    public function getBalance(): float
    {
        return (float)($this->balanceMinor / $this->currency->getScale());
    }

    #[Groups(['AccountCreate'])]
    public function setBalance(float $balance): void
    {
        $this->balanceMinor = (int)round($balance * $this->currency->getScale(), PHP_ROUND_HALF_DOWN);
    }
}
