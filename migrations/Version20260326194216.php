<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326194216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, fantasy_name VARCHAR(255) DEFAULT NULL, document VARCHAR(255) DEFAULT NULL, mail VARCHAR(255) DEFAULT NULL, phone1 VARCHAR(20) DEFAULT NULL, phone2 VARCHAR(20) DEFAULT NULL, obs LONGTEXT DEFAULT NULL, status VARCHAR(20) DEFAULT NULL, logo_filename VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE client_project (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, full_description LONGTEXT DEFAULT NULL, date_end DATE DEFAULT NULL, date_start DATE DEFAULT NULL, obs LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, logo_filename VARCHAR(255) DEFAULT NULL, client_id INT NOT NULL, INDEX IDX_7D8E949319EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE client_project_attached (id INT AUTO_INCREMENT NOT NULL, file VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, client_project_id INT NOT NULL, INDEX IDX_71206CA8863922DD (client_project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE client_project_history (id INT AUTO_INCREMENT NOT NULL, occurred_at DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, summary LONGTEXT DEFAULT NULL, transcript LONGTEXT DEFAULT NULL, audio_filename VARCHAR(255) DEFAULT NULL, client_project_id INT NOT NULL, INDEX IDX_CC8221DD863922DD (client_project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE client_project ADD CONSTRAINT FK_7D8E949319EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_project_attached ADD CONSTRAINT FK_71206CA8863922DD FOREIGN KEY (client_project_id) REFERENCES client_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_project_history ADD CONSTRAINT FK_CC8221DD863922DD FOREIGN KEY (client_project_id) REFERENCES client_project (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_project DROP FOREIGN KEY FK_7D8E949319EB6921');
        $this->addSql('ALTER TABLE client_project_attached DROP FOREIGN KEY FK_71206CA8863922DD');
        $this->addSql('ALTER TABLE client_project_history DROP FOREIGN KEY FK_CC8221DD863922DD');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE client_project');
        $this->addSql('DROP TABLE client_project_attached');
        $this->addSql('DROP TABLE client_project_history');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
