<?php

namespace App\Tests\Behat;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\BusinessPartner;
use App\Entity\Transaction;
use App\Enums\BusinessPartnerStatusEnum;
use App\Enums\LegalFormEnum;
use App\Enums\TransactionTypeEnum;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Psr\Log\NullLogger;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class AppContext implements Context
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly IriConverterInterface $iriConverter
    ) {
    }

    /** @BeforeScenario */
    public function setup(): void
    {
        $manager = $this->getManager();
        $manager->getConnection()->getConfiguration()->setMiddlewares([new Middleware(new NullLogger())]);

        $purger = new ORMPurger($manager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();

        $manager->clear();
        $manager->getConnection()->executeStatement('DELETE FROM SQLITE_SEQUENCE');
    }

    /**
     * @Given there is a business partner with data:
     */
    public function createBusinessPartner(TableNode $tableNode)
    {
        $businessPartnerArray = $this->transformTableToArray($tableNode);

        $manager = $this->getManager();

        foreach ($businessPartnerArray as $businessPartnerItem) {
            $businessPartner = new BusinessPartner();
            $businessPartner->setName($businessPartnerItem['name']);
            $businessPartner->setStatus($businessPartnerItem['status']);
            $businessPartner->setLegalForm($businessPartnerItem['legalForm']);
            $businessPartner->setBalance($businessPartnerItem['balance']);
            $businessPartner->setAddress($businessPartnerItem['address']);
            $businessPartner->setCity($businessPartnerItem['city']);
            $businessPartner->setZip($businessPartnerItem['zip']);
            $businessPartner->setCountry($businessPartnerItem['country']);

            $manager->persist($businessPartner);
        }

        $manager->flush();
    }

    /**
     * @Given create a transaction with data:
     */
    public function createTransaction(TableNode $tableNode)
    {
        $transactionArray = $this->transformTableToArray($tableNode);

        $manager = $this->getManager();

        foreach ($transactionArray as $transactionItem) {
            $transaction = new Transaction();
            $transaction->setName($transactionItem['name']);
            $transaction->setAmount($transactionItem['amount']);
            $transaction->setDate($transactionItem['date']);
            $transaction->setExecuted($transactionItem['executed']);
            $transaction->setType($transactionItem['type']);
            $transaction->setCountry($transactionItem['country']);
            $transaction->setIban($transactionItem['iban']);
            $transaction->setIban($transactionItem['iban']);

            /** @var BusinessPartner $businessPartner */
            $businessPartner = $transactionItem['businessPartner'];

            if ($businessPartner instanceof BusinessPartner) {
                $transaction->setBusinessPartner($businessPartner);
            }

            $manager->persist($transaction);
        }

        $manager->flush();
    }

    private function getManager(): EntityManagerInterface|ObjectManager
    {
        return $this->managerRegistry->getManager();
    }

    private function transformTableToArray(?TableNode $table): array
    {
        if (null === $table) {
            return [];
        }

        $rows = $table->getRows();

        if (2 > count($rows)) {
            throw new Exception('Table have to contain two rows at least');
        }

        $headerRow = $rows[0];
        unset($rows[0]);

        $array = [];

        foreach ($rows as $row) {
            $item = [];
            foreach ($headerRow as $key => $name) {
                $item[$name] = $this->resolveValue($name, $row[$key]);
            }
            $array[] = $item;
        }

        return $array;
    }

    private function resolveValue(string $name, mixed $value): mixed
    {
        if(in_array($value, ['false', 0, '0'])) {
            $value = false;
        }

        if(in_array($value, ['true', 1, '1'])) {
            $value = true;
        }

        switch ($name) {
            case 'status':
                $value = BusinessPartnerStatusEnum::tryFrom($value);
                break;
            case 'date':
                $value = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
                break;
            case 'legalForm':
                $value = LegalFormEnum::tryFrom($value);
                break;
            case 'type':
                $value = TransactionTypeEnum::tryFrom($value);
                break;
            case 'transaction':
            case 'businessPartner':
                $value = $this->iriConverter->getResourceFromIri($value);
                break;
        }

        return $value;
    }
}