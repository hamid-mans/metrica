<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120193836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE step (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, cost VARCHAR(255) DEFAULT NULL, income VARCHAR(255) DEFAULT NULL, step_number INT NOT NULL, workflow_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_43B9FE3C2C7C2CBA (workflow_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C2C7C2CBA FOREIGN KEY (workflow_id) REFERENCES workflow (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3C2C7C2CBA');
        $this->addSql('DROP TABLE step');
    }
}
