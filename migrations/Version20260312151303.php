<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312151303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjust client_id to NOT NULL on client_project';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_project CHANGE client_id client_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_project CHANGE client_id client_id INT DEFAULT NULL');
    }
}