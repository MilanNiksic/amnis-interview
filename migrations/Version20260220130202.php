<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220130202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__business_partners AS SELECT id, name, status, legal_form, address, city, zip, country FROM business_partners');
        $this->addSql('DROP TABLE business_partners');
        $this->addSql('CREATE TABLE business_partners (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, legal_form VARCHAR(255) NOT NULL, address VARCHAR(70) NOT NULL, city VARCHAR(35) NOT NULL, zip VARCHAR(16) NOT NULL, country VARCHAR(2) NOT NULL)');
        $this->addSql('INSERT INTO business_partners (id, name, status, legal_form, address, city, zip, country) SELECT id, name, status, legal_form, address, city, zip, country FROM __temp__business_partners');
        $this->addSql('DROP TABLE __temp__business_partners');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE business_partners ADD COLUMN balance NUMERIC(10, 2) NOT NULL');
    }
}
