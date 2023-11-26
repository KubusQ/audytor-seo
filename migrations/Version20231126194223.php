<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231126194223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audits_data ADD id INT AUTO_INCREMENT NOT NULL, CHANGE uid uid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE data data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audits_data MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON audits_data');
        $this->addSql('ALTER TABLE audits_data DROP id, CHANGE uid uid VARCHAR(255) NOT NULL, CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE audits_data ADD PRIMARY KEY (uid)');
    }
}
