<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260217070239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Generate Drawer schema.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE sock (identifier VARCHAR NOT NULL, email_address VARCHAR NOT NULL, name VARCHAR NOT NULL, PRIMARY KEY (identifier))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sock');
    }
}
