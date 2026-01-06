<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106183916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE registro_historico_anexo (id INT AUTO_INCREMENT NOT NULL, file VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, historico_anexo_id INT DEFAULT NULL, INDEX IDX_EA37EAF0A2611E01 (historico_anexo_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE registro_historico_anexo ADD CONSTRAINT FK_EA37EAF0A2611E01 FOREIGN KEY (historico_anexo_id) REFERENCES registro_historico (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registro_historico_anexo DROP FOREIGN KEY FK_EA37EAF0A2611E01');
        $this->addSql('DROP TABLE registro_historico_anexo');
    }
}
