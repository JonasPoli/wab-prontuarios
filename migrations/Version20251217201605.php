<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217201605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registro_historico ADD data_registro DATETIME NOT NULL, ADD tipo_registro VARCHAR(50) DEFAULT NULL, ADD titulo VARCHAR(255) DEFAULT NULL, ADD descricao LONGTEXT NOT NULL, ADD visivel_para_cliente TINYINT DEFAULT 0 NOT NULL, ADD tags VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD projeto_id INT NOT NULL');
        $this->addSql('ALTER TABLE registro_historico ADD CONSTRAINT FK_18BAAD6243B58490 FOREIGN KEY (projeto_id) REFERENCES projeto (id)');
        $this->addSql('CREATE INDEX IDX_18BAAD6243B58490 ON registro_historico (projeto_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registro_historico DROP FOREIGN KEY FK_18BAAD6243B58490');
        $this->addSql('DROP INDEX IDX_18BAAD6243B58490 ON registro_historico');
        $this->addSql('ALTER TABLE registro_historico DROP data_registro, DROP tipo_registro, DROP titulo, DROP descricao, DROP visivel_para_cliente, DROP tags, DROP created_at, DROP updated_at, DROP projeto_id');
    }
}
