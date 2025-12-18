<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218173347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registro_historico ADD usuario_autor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE registro_historico ADD CONSTRAINT FK_18BAAD62FC15A927 FOREIGN KEY (usuario_autor_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_18BAAD62FC15A927 ON registro_historico (usuario_autor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registro_historico DROP FOREIGN KEY FK_18BAAD62FC15A927');
        $this->addSql('DROP INDEX IDX_18BAAD62FC15A927 ON registro_historico');
        $this->addSql('ALTER TABLE registro_historico DROP usuario_autor_id');
    }
}
