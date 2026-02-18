<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240710114217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE business_partners (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                status VARCHAR(255) NOT NULL,
                legal_form VARCHAR(255) NOT NULL,
                balance NUMERIC(10, 2) NOT NULL,
                address VARCHAR(70) NOT NULL,
                city VARCHAR(35) NOT NULL,
                zip VARCHAR(16) NOT NULL,
                country VARCHAR(2) NOT NULL
            )
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                business_partner_id INTEGER NOT NULL,
                amount NUMERIC(10,
                2) NOT NULL,
                name VARCHAR(255) NOT NULL,
                date DATETIME NOT NULL --(DC2Type:datetime_immutable)
                ,
                executed BOOLEAN NOT NULL,
                type VARCHAR(50) NOT NULL,
                country VARCHAR(2) NOT NULL,
                iban VARCHAR(34) NOT NULL,
                CONSTRAINT FK_723705D15330F055 FOREIGN KEY (business_partner_id) REFERENCES business_partners (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_723705D15330F055 ON transactions (business_partner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE business_partners');
        $this->addSql('DROP TABLE transactions');
    }
}
