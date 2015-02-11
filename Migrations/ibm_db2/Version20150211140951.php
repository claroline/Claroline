<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/11 02:09:53
 */
class Version20150211140951 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN start_date TIMESTAMP(0) DEFAULT NULL 
            ADD COLUMN end_date TIMESTAMP(0) DEFAULT NULL 
            ADD COLUMN is_access_date SMALLINT NOT NULL WITH DEFAULT 
            ADD COLUMN workspace_type INTEGER DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN start_date 
            DROP COLUMN end_date 
            DROP COLUMN is_access_date 
            DROP COLUMN workspace_type
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE claro_workspace')
        ");
    }
}