<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219102559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE currency_exchange_rates (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_currency_id INTEGER NOT NULL, to_currency_id INTEGER NOT NULL, rate NUMERIC(18, 8) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_B20B9756A66BB013 FOREIGN KEY (from_currency_id) REFERENCES currencies (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B20B975616B7BF15 FOREIGN KEY (to_currency_id) REFERENCES currencies (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B20B9756A66BB013 ON currency_exchange_rates (from_currency_id)');
        $this->addSql('CREATE INDEX IDX_B20B975616B7BF15 ON currency_exchange_rates (to_currency_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__currencies AS SELECT id, code, name, scale, is_active FROM currencies');
        $this->addSql('DROP TABLE currencies');
        $this->addSql('CREATE TABLE currencies (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(255) NOT NULL, scale SMALLINT NOT NULL, is_active BOOLEAN DEFAULT 1 NOT NULL)');
        $this->addSql('INSERT INTO currencies (id, code, name, scale, is_active) SELECT id, code, name, scale, is_active FROM __temp__currencies');
        $this->addSql('DROP TABLE __temp__currencies');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_37C4469377153098 ON currencies (code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE currency_exchange_rates');
        $this->addSql('CREATE TEMPORARY TABLE __temp__currencies AS SELECT id, code, name, scale, is_active FROM currencies');
        $this->addSql('DROP TABLE currencies');
        $this->addSql('CREATE TABLE currencies (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(255) NOT NULL, scale SMALLINT NOT NULL, is_active BOOLEAN DEFAULT 1 NOT NULL)');
        $this->addSql('INSERT INTO currencies (id, code, name, scale, is_active) SELECT id, code, name, scale, is_active FROM __temp__currencies');
        $this->addSql('DROP TABLE __temp__currencies');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6956883F77153098 ON currencies (code)');
    }
}
