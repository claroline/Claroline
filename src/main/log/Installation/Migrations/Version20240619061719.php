<?php

namespace Claroline\LogBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/06/19 06:18:29
 */
final class Version20240619061719 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_operational 
            ADD context_name VARCHAR(255) NOT NULL, 
            ADD context_id VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_log_operational 
            DROP context_name, 
            DROP context_id
        ');
    }
}
