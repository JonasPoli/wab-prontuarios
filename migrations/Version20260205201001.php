<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260205201001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_project_history (id INT AUTO_INCREMENT NOT NULL, occurred_at DATETIME DEFAULT NULL, summary LONGTEXT DEFAULT NULL, transcript LONGTEXT DEFAULT NULL, history_id INT DEFAULT NULL, INDEX IDX_CC8221DD1E058452 (history_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE client_project_history ADD CONSTRAINT FK_CC8221DD1E058452 FOREIGN KEY (history_id) REFERENCES client_project (id)');
        $this->addSql('ALTER TABLE client CHANGE obs obs LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE client_project CHANGE full_description full_description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_project_history DROP FOREIGN KEY FK_CC8221DD1E058452');
        $this->addSql('DROP TABLE client_project_history');
        $this->addSql('ALTER TABLE client CHANGE obs obs LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE client_project CHANGE full_description full_description LONGTEXT NOT NULL');
    }
}
