<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/09 02:04:30
 */
class Version20150309140427 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'ordered_tool_unique_tool_ws_usr'
            ) 
            ALTER TABLE claro_ordered_tool 
            DROP CONSTRAINT ordered_tool_unique_tool_ws_usr ELSE 
            DROP INDEX ordered_tool_unique_tool_ws_usr ON claro_ordered_tool
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD ordered_tool_type INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD is_locked BIT NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_usr_type ON claro_ordered_tool (
                tool_id, workspace_id, user_id, ordered_tool_type
            ) 
            WHERE tool_id IS NOT NULL 
            AND workspace_id IS NOT NULL 
            AND user_id IS NOT NULL 
            AND ordered_tool_type IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'ordered_tool_unique_tool_ws_usr_type'
            ) 
            ALTER TABLE claro_ordered_tool 
            DROP CONSTRAINT ordered_tool_unique_tool_ws_usr_type ELSE 
            DROP INDEX ordered_tool_unique_tool_ws_usr_type ON claro_ordered_tool
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP COLUMN ordered_tool_type
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP COLUMN is_locked
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_usr ON claro_ordered_tool (tool_id, workspace_id, user_id) 
            WHERE tool_id IS NOT NULL 
            AND workspace_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
    }
}