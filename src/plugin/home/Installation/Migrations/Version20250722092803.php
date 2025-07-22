<?php

namespace Claroline\HomeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/07/22 09:28:04
 */
final class Version20250722092803 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP color
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD color VARCHAR(255) DEFAULT NULL
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
