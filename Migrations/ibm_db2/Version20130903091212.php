<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 09:12:15
 */
class Version20130903091212 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log 
            ADD COLUMN is_displayed_in_admin SMALLINT NOT NULL 
            ADD COLUMN is_displayed_in_workspace SMALLINT NOT NULL 
            DROP COLUMN child_type 
            DROP COLUMN child_action
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log 
            ADD COLUMN child_type VARCHAR(255) DEFAULT NULL 
            ADD COLUMN child_action VARCHAR(255) DEFAULT NULL 
            DROP COLUMN is_displayed_in_admin 
            DROP COLUMN is_displayed_in_workspace
        ");
    }
}