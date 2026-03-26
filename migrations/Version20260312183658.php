<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312183658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjust anexos column to JSON';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_project MODIFY anexos JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_project MODIFY anexos VARCHAR(255) DEFAULT NULL');
    }
}