<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/09 02:04:29
 */
class Version20150309140427 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_ws_usr
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD COLUMN ordered_tool_type INTEGER NOT NULL WITH DEFAULT 
            ADD COLUMN is_locked SMALLINT NOT NULL WITH DEFAULT
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_usr_type ON claro_ordered_tool (
                tool_id, workspace_id, user_id, ordered_tool_type
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_ws_usr_type
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP COLUMN ordered_tool_type 
            DROP COLUMN is_locked
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD (
                'REORG TABLE claro_ordered_tool'
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_usr ON claro_ordered_tool (tool_id, workspace_id, user_id)
        ");
    }
}