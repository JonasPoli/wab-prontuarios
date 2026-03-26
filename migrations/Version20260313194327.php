<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313194327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attached (id INT AUTO_INCREMENT NOT NULL, client_project JSON DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE client_project_attached (id INT AUTO_INCREMENT NOT NULL, file VARCHAR(255) DEFAULT NULL, projeto_id INT DEFAULT NULL, INDEX IDX_71206CA843B58490 (projeto_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE client_project_attached ADD CONSTRAINT FK_71206CA843B58490 FOREIGN KEY (projeto_id) REFERENCES client_project (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_project_attached DROP FOREIGN KEY FK_71206CA843B58490');
        $this->addSql('DROP TABLE attached');
        $this->addSql('DROP TABLE client_project_attached');
    }
}
