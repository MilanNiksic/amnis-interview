<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\PayinController;
use App\Controller\Api\PayoutController;
use App\Controller\Api\PayoutExecutionController;
use App\Enums\TransactionTypeEnum;
use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transactions')]
#[ApiResource(
    operations: [
        new Get(),
        new Post(
            uriTemplate: '/transactions/payin',
            controller: PayinController::class,
            denormalizationContext: ['groups' => ['TransactionCreate']]
        ),
        new Post(
            uriTemplate: '/transactions/payout',
            controller: PayoutController::class,
            denormalizationContext: ['groups' => ['TransactionCreate']]
        ),
        new Patch(
            uriTemplate: '/transactions/{id}/payout/execute',
            controller: PayoutExecutionController::class,
            denormalizationContext: ['groups' => ['TransactionPatch']]
        ),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['TransactionView']]
)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['TransactionView'])]
    private int $id;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(1)]
    #[Groups(['TransactionView', 'TransactionCreate'])]
    private ?string $amount;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups(['TransactionView', 'TransactionCreate'])]
    private string $name;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotBlank]
    #[Groups(['TransactionView', 'TransactionCreate'])]
    private DateTimeImmutable $date;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['TransactionView'])]
    private bool $executed = false;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: TransactionTypeEnum::class)]
    #[Assert\Type(type: TransactionTypeEnum::class, message: 'Choose a valid type.')]
    #[Groups(['TransactionView'])]
    private TransactionTypeEnum $type;

    #[ORM\Column(type: Types::STRING, length: 2)]
    #[Assert\Length(min: 1, max: 2)]
    #[Groups(['TransactionView', 'TransactionCreate'])]
    private string $country;

    #[ORM\Column(type: Types::STRING, length: 34)]
    #[Assert\Length(min: 1, max: 34)]
    #[Groups(['TransactionView', 'TransactionCreate'])]
    private string $iban;

    #[ORM\ManyToOne(targetEntity: BusinessPartner::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['TransactionView', 'TransactionCreate'])]
    private BusinessPartner $businessPartner;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): void
    {
        $this->amount = $amount;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    public function setExecuted(bool $executed): void
    {
        $this->executed = $executed;
    }

    public function getType(): TransactionTypeEnum
    {
        return $this->type;
    }

    public function setType(TransactionTypeEnum $type): void
    {
        $this->type = $type;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function setIban(string $iban): void
    {
        $this->iban = $iban;
    }

    public function getBusinessPartner(): BusinessPartner
    {
        return $this->businessPartner;
    }

    public function setBusinessPartner(BusinessPartner $businessPartner): void
    {
        $this->businessPartner = $businessPartner;
    }
}