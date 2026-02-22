<?php

namespace App\Tests\Behat;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Account;
use App\Entity\BusinessPartner;
use App\Entity\Currency;
use App\Entity\CurrencyExchangeRate;
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
            $businessPartner->setAddress($businessPartnerItem['address']);
            $businessPartner->setCity($businessPartnerItem['city']);
            $businessPartner->setZip($businessPartnerItem['zip']);
            $businessPartner->setCountry($businessPartnerItem['country']);

            $manager->persist($businessPartner);
        }

        $manager->flush();
    }

    /**
     * @Given there is a currency with data:
     */
    public function createCurrency(TableNode $tableNode)
    {
        $currencyArray = $this->transformTableToArray($tableNode);

        $manager = $this->getManager();

        foreach ($currencyArray as $currencyItem) {
            $currency = new Currency();
            $currency->setCode($currencyItem['code']);
            $currency->setScale($currencyItem['scale']);
            $currency->setIsActive($currencyItem['isActive']);
            // Set name - use provided value or default to code if not provided
            $name = $currencyItem['name'] ?? $currencyItem['code'];
            $currency->setName($name);

            $manager->persist($currency);
        }

        $manager->flush();
    }

    /**
     * @Given there is an exchange rate with data:
     */
    public function createCurrencyExchangeRate(TableNode $tableNode)
    {
        $exchangeRateArray = $this->transformTableToArray($tableNode);

        $manager = $this->getManager();

        foreach ($exchangeRateArray as $exchangeRateItem) {
            $exchangeRate = new CurrencyExchangeRate();
            $exchangeRate->setFromCurrency($exchangeRateItem['fromCurrency']);
            $exchangeRate->setToCurrency($exchangeRateItem['toCurrency']);
            $exchangeRate->setRate((float)$exchangeRateItem['rate']);

            $manager->persist($exchangeRate);
        }

        $manager->flush();
    }

    /**
     * @Given there is an account with data:
     */
    public function createAccount(TableNode $tableNode)
    {
        $accountArray = $this->transformTableToArray($tableNode);

        $manager = $this->getManager();

        foreach ($accountArray as $accountItem) {
            $account = new Account();
            $account->setName($accountItem['name']);
            $account->setAccountNumber($accountItem['accountNumber']);
            $account->setBusinessPartner($accountItem['businessPartner']);
            $account->setCurrency($accountItem['currency']);
            $account->setBalance((float)$accountItem['balance']);

            $manager->persist($account);
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

            /** @var BusinessPartner $businessPartner */
            $businessPartner = $transactionItem['businessPartner'];

            if ($businessPartner instanceof BusinessPartner) {
                $transaction->setBusinessPartner($businessPartner);
            }

            /** @var Account $account */
            $account = $transactionItem['account'];

            if ($account instanceof Account) {
                $transaction->setAccount($account);
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
            case 'account':
            case 'currency':
            case 'fromCurrency':
            case 'toCurrency':
                // If it looks like an IRI already (starts with /), convert it directly
                if (is_string($value) && str_starts_with($value, '/')) {
                    $value = $this->iriConverter->getResourceFromIri($value);
                } else if (is_string($value) && strlen($value) === 3 && ctype_alpha($value)) {
                    // If it's a 3-letter currency code, find the currency by code
                    $manager = $this->getManager();
                    $value = $manager->getRepository(Currency::class)->findOneBy(['code' => strtoupper($value)]);
                }
                break;
        }

        return $value;
    }
}