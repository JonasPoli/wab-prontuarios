<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312183015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'No-op migration because anexos column already exists';
    }

    public function up(Schema $schema): void
    {
        // coluna anexos já existe no banco
    }

    public function down(Schema $schema): void
    {
        // sem ação
    }
}