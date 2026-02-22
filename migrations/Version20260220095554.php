<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220095554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__exchanges AS SELECT id, from_amount, to_amount, exchange_rate, created_at FROM exchanges');
        $this->addSql('DROP TABLE exchanges');
        $this->addSql('CREATE TABLE exchanges (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_currency_id INTEGER NOT NULL, to_currency_id INTEGER NOT NULL, from_amount BIGINT UNSIGNED NOT NULL, to_amount BIGINT UNSIGNED NOT NULL, exchange_rate NUMERIC(18, 8) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_32043D23A66BB013 FOREIGN KEY (from_currency_id) REFERENCES currencies (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_32043D2316B7BF15 FOREIGN KEY (to_currency_id) REFERENCES currencies (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO exchanges (id, from_amount, to_amount, exchange_rate, created_at) SELECT id, from_amount, to_amount, exchange_rate, created_at FROM __temp__exchanges');
        $this->addSql('DROP TABLE __temp__exchanges');
        $this->addSql('CREATE INDEX IDX_32043D23A66BB013 ON exchanges (from_currency_id)');
        $this->addSql('CREATE INDEX IDX_32043D2316B7BF15 ON exchanges (to_currency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__exchanges AS SELECT id, from_amount, to_amount, exchange_rate, created_at FROM exchanges');
        $this->addSql('DROP TABLE exchanges');
        $this->addSql('CREATE TABLE exchanges (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_account_id INTEGER NOT NULL, to_account_id INTEGER NOT NULL, from_amount BIGINT UNSIGNED NOT NULL, to_amount BIGINT UNSIGNED NOT NULL, exchange_rate NUMERIC(18, 8) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_32043D23B0CF99BD FOREIGN KEY (from_account_id) REFERENCES accounts (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_32043D23BC58BDC7 FOREIGN KEY (to_account_id) REFERENCES accounts (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO exchanges (id, from_amount, to_amount, exchange_rate, created_at) SELECT id, from_amount, to_amount, exchange_rate, created_at FROM __temp__exchanges');
        $this->addSql('DROP TABLE __temp__exchanges');
        $this->addSql('CREATE INDEX IDX_32043D23BC58BDC7 ON exchanges (to_account_id)');
        $this->addSql('CREATE INDEX IDX_32043D23B0CF99BD ON exchanges (from_account_id)');
    }
}
