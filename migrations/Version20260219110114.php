<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219110114 extends AbstractMigration
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
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE accounts');
    }
}
