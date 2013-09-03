<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 09:12:16
 */
class Version20130903091212 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log 
            ADD is_displayed_in_admin BOOLEAN NOT NULL, 
            ADD is_displayed_in_workspace BOOLEAN NOT NULL, 
            DROP child_type, 
            DROP child_action
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log 
            ADD child_type VARCHAR(255) DEFAULT NULL, 
            ADD child_action VARCHAR(255) DEFAULT NULL, 
            DROP is_displayed_in_admin, 
            DROP is_displayed_in_workspace
        ");
    }
}