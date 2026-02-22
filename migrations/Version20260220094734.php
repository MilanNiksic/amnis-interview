<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220094734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE accounts (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, business_partner_id INTEGER NOT NULL, currency_id INTEGER NOT NULL, balance_minor BIGINT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, account_number VARCHAR(50) NOT NULL, is_active BOOLEAN DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL, CONSTRAINT FK_CAC89EAC5330F055 FOREIGN KEY (business_partner_id) REFERENCES business_partners (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CAC89EAC38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CAC89EAC5330F055 ON accounts (business_partner_id)');
        $this->addSql('CREATE INDEX IDX_CAC89EAC38248176 ON accounts (currency_id)');
        $this->addSql('CREATE TABLE business_partners (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, legal_form VARCHAR(255) NOT NULL, address VARCHAR(70) NOT NULL, city VARCHAR(35) NOT NULL, zip VARCHAR(16) NOT NULL, country VARCHAR(2) NOT NULL, balance NUMERIC(10, 2) NOT NULL)');
        $this->addSql('CREATE TABLE currencies (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(255) NOT NULL, scale SMALLINT NOT NULL, is_active BOOLEAN DEFAULT 1 NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_37C4469377153098 ON currencies (code)');
        $this->addSql('CREATE TABLE currency_exchange_rates (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_currency_id INTEGER NOT NULL, to_currency_id INTEGER NOT NULL, rate NUMERIC(18, 8) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_B20B9756A66BB013 FOREIGN KEY (from_currency_id) REFERENCES currencies (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B20B975616B7BF15 FOREIGN KEY (to_currency_id) REFERENCES currencies (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B20B9756A66BB013 ON currency_exchange_rates (from_currency_id)');
        $this->addSql('CREATE INDEX IDX_B20B975616B7BF15 ON currency_exchange_rates (to_currency_id)');
        $this->addSql('CREATE TABLE exchanges (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_account_id INTEGER NOT NULL, to_account_id INTEGER NOT NULL, from_amount BIGINT UNSIGNED NOT NULL, to_amount BIGINT UNSIGNED NOT NULL, exchange_rate NUMERIC(18, 8) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_32043D23B0CF99BD FOREIGN KEY (from_account_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_32043D23BC58BDC7 FOREIGN KEY (to_account_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_32043D23B0CF99BD ON exchanges (from_account_id)');
        $this->addSql('CREATE INDEX IDX_32043D23BC58BDC7 ON exchanges (to_account_id)');
        $this->addSql('CREATE TABLE transactions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, business_partner_id INTEGER NOT NULL, account_id INTEGER NOT NULL, exchange_id INTEGER DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , executed BOOLEAN NOT NULL, type VARCHAR(50) NOT NULL, country VARCHAR(2) NOT NULL, iban VARCHAR(34) NOT NULL, CONSTRAINT FK_EAA81A4C5330F055 FOREIGN KEY (business_partner_id) REFERENCES business_partners (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EAA81A4C9B6B5FBA FOREIGN KEY (account_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EAA81A4C68AFD1A0 FOREIGN KEY (exchange_id) REFERENCES exchanges (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_EAA81A4C5330F055 ON transactions (business_partner_id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4C9B6B5FBA ON transactions (account_id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4C68AFD1A0 ON transactions (exchange_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE accounts');
        $this->addSql('DROP TABLE business_partners');
        $this->addSql('DROP TABLE currencies');
        $this->addSql('DROP TABLE currency_exchange_rates');
        $this->addSql('DROP TABLE exchanges');
        $this->addSql('DROP TABLE transactions');
    }
}
