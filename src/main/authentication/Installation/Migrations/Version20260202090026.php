<?php

namespace Claroline\AuthenticationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2026/02/02 09:00:28
 */
final class Version20260202090026 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_authentication_parameters CHANGE minLength minLength INT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_authentication_parameters CHANGE minLength minLength INT NOT NULL
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
