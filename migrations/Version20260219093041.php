<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219093041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE currencies (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(255) NOT NULL, scale SMALLINT NOT NULL, is_active BOOLEAN DEFAULT 1 NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6956883F77153098 ON currencies (code)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__transactions AS SELECT id, business_partner_id, amount, name, date, executed, type, country, iban FROM transactions');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('CREATE TABLE transactions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, business_partner_id INTEGER NOT NULL, amount NUMERIC(10, 2) NOT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , executed BOOLEAN NOT NULL, type VARCHAR(50) NOT NULL, country VARCHAR(2) NOT NULL, iban VARCHAR(34) NOT NULL, CONSTRAINT FK_723705D15330F055 FOREIGN KEY (business_partner_id) REFERENCES business_partners (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO transactions (id, business_partner_id, amount, name, date, executed, type, country, iban) SELECT id, business_partner_id, amount, name, date, executed, type, country, iban FROM __temp__transactions');
        $this->addSql('DROP TABLE __temp__transactions');
        $this->addSql('CREATE INDEX IDX_EAA81A4C5330F055 ON transactions (business_partner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE currencies');
        $this->addSql('CREATE TEMPORARY TABLE __temp__transactions AS SELECT id, business_partner_id, amount, name, date, executed, type, country, iban FROM transactions');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('CREATE TABLE transactions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, business_partner_id INTEGER NOT NULL, amount NUMERIC(10, 2) NOT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , executed BOOLEAN NOT NULL, type VARCHAR(50) NOT NULL, country VARCHAR(2) NOT NULL, iban VARCHAR(34) NOT NULL, CONSTRAINT FK_EAA81A4C5330F055 FOREIGN KEY (business_partner_id) REFERENCES business_partners (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO transactions (id, business_partner_id, amount, name, date, executed, type, country, iban) SELECT id, business_partner_id, amount, name, date, executed, type, country, iban FROM __temp__transactions');
        $this->addSql('DROP TABLE __temp__transactions');
        $this->addSql('CREATE INDEX IDX_723705D15330F055 ON transactions (business_partner_id)');
    }
}
