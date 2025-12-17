<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217200622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cliente (id INT AUTO_INCREMENT NOT NULL, tipo VARCHAR(50) NOT NULL, nome VARCHAR(255) NOT NULL, apelido_fantasia VARCHAR(255) DEFAULT NULL, documento VARCHAR(20) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telefone_principal VARCHAR(20) DEFAULT NULL, telefone_secundario VARCHAR(20) DEFAULT NULL, observacoes LONGTEXT DEFAULT NULL, status TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE projeto (id INT AUTO_INCREMENT NOT NULL, titulo VARCHAR(255) NOT NULL, codigo_interno VARCHAR(50) DEFAULT NULL, descricao_resumida VARCHAR(500) DEFAULT NULL, descricao_detalhada LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, data_inicio_prevista DATE DEFAULT NULL, data_fim_prevista DATE DEFAULT NULL, data_inicio_real DATE DEFAULT NULL, data_fim_real DATE DEFAULT NULL, tags VARCHAR(255) DEFAULT NULL, observacoes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, cliente_id INT NOT NULL, responsavel_interno_id INT DEFAULT NULL, INDEX IDX_A0559D94DE734E51 (cliente_id), INDEX IDX_A0559D94A0EB0F4D (responsavel_interno_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE projeto ADD CONSTRAINT FK_A0559D94DE734E51 FOREIGN KEY (cliente_id) REFERENCES cliente (id)');
        $this->addSql('ALTER TABLE projeto ADD CONSTRAINT FK_A0559D94A0EB0F4D FOREIGN KEY (responsavel_interno_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projeto DROP FOREIGN KEY FK_A0559D94DE734E51');
        $this->addSql('ALTER TABLE projeto DROP FOREIGN KEY FK_A0559D94A0EB0F4D');
        $this->addSql('DROP TABLE cliente');
        $this->addSql('DROP TABLE projeto');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
