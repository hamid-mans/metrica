<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120194915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY `FK_43B9FE3C2C7C2CBA`');
        $this->addSql('DROP INDEX UNIQ_43B9FE3C2C7C2CBA ON step');
        $this->addSql('ALTER TABLE step DROP workflow_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE step ADD workflow_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT `FK_43B9FE3C2C7C2CBA` FOREIGN KEY (workflow_id) REFERENCES workflow (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_43B9FE3C2C7C2CBA ON step (workflow_id)');
    }
}
