<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401193905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, context_snapshot LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, client_id INT NOT NULL, INDEX IDX_8A8E26E919EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE conversation_message (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(20) NOT NULL, content LONGTEXT NOT NULL, tokens_used INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, conversation_id INT NOT NULL, INDEX IDX_2DEB3E759AC0396 (conversation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E759AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E919EB6921');
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E759AC0396');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE conversation_message');
    }
}
