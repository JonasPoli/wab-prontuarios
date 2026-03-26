<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260317155459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_project_history DROP FOREIGN KEY `FK_CC8221DD1E058452`');
        $this->addSql('DROP INDEX IDX_CC8221DD1E058452 ON client_project_history');
        $this->addSql('ALTER TABLE client_project_history CHANGE history_id client_project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client_project_history ADD CONSTRAINT FK_CC8221DD863922DD FOREIGN KEY (client_project_id) REFERENCES client_project (id)');
        $this->addSql('CREATE INDEX IDX_CC8221DD863922DD ON client_project_history (client_project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_project_history DROP FOREIGN KEY FK_CC8221DD863922DD');
        $this->addSql('DROP INDEX IDX_CC8221DD863922DD ON client_project_history');
        $this->addSql('ALTER TABLE client_project_history CHANGE client_project_id history_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client_project_history ADD CONSTRAINT `FK_CC8221DD1E058452` FOREIGN KEY (history_id) REFERENCES client_project (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_CC8221DD1E058452 ON client_project_history (history_id)');
    }
}
