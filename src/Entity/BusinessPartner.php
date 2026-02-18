<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Enums\BusinessPartnerStatusEnum;
use App\Enums\LegalFormEnum;
use App\Repository\BusinessPartnerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BusinessPartnerRepository::class)]
#[ORM\Table(name: 'business_partners')]
#[ApiResource(
    operations: [
        new Get(),
        new Post(
            denormalizationContext: ['groups' => ['BusinessPartnerCreate']]
        ),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['BusinessPartnerView']]
)]
class BusinessPartner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['BusinessPartnerView'])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups(['BusinessPartnerView', 'BusinessPartnerCreate'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: BusinessPartnerStatusEnum::class)]
    #[Assert\Type(type: BusinessPartnerStatusEnum::class, message: 'Choose a valid status.')]
    #[Groups(['BusinessPartnerView', 'BusinessPartnerCreate'])]
    private BusinessPartnerStatusEnum $status;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: LegalFormEnum::class)]
    #[Assert\Type(type: LegalFormEnum::class, message: 'Choose a valid legal form.')]
    #[Groups(['BusinessPartnerView', 'BusinessPartnerCreate'])]
    private LegalFormEnum $legalForm;

    #[ORM\Column(type: Types::STRING, length: 70)]
    #[Assert\Length(min: 1, max: 70)]
    #[Groups(['BusinessPartnerView', 'BusinessPartnerCreate'])]
    private string $address;

    #[ORM\Column(type: Types::STRING, length: 35)]
    #[Assert\Length(min: 1, max: 35)]
    #[Groups(['BusinessPartnerView', 'BusinessPartnerCreate'])]
    private string $city;

    #[ORM\Column(type: Types::STRING, length: 16)]
    #[Assert\Length(min: 1, max: 16)]
    #[Groups(['BusinessPartnerView', 'BusinessPartnerCreate'])]
    private string $zip;

    #[ORM\Column(type: Types::STRING, length: 2)]
    #[Assert\Length(min: 1, max: 2)]
    #[Groups(['BusinessPartnerView', 'BusinessPartnerCreate'])]
    private string $country;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['BusinessPartnerView'])]
    private ?string $balance = '0';

    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'businessPartner')]
    #[Groups(['BusinessPartnerView'])]
    private Collection $transactions;

    public function __toString(): string
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStatus(): BusinessPartnerStatusEnum
    {
        return $this->status;
    }

    public function setStatus(BusinessPartnerStatusEnum $status): void
    {
        $this->status = $status;
    }

    public function getLegalForm(): LegalFormEnum
    {
        return $this->legalForm;
    }

    public function setLegalForm(LegalFormEnum $legalForm): void
    {
        $this->legalForm = $legalForm;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): void
    {
        $this->balance = $balance;
    }

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function setTransactions(Collection $transactions): void
    {
        $this->transactions = $transactions;
    }

    public function addTransaction(Transaction $transaction): void
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
        }
    }

    public function removeTransaction(Transaction $transaction): void
    {
        $this->transactions->removeElement($transaction);
    }
}
