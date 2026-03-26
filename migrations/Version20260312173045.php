<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312173045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create anexos column on client_project';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_project ADD anexos JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_project DROP anexos');
    }
}