<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/20 05:19:24
 */
class Version20150420171923 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD content_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP COLUMN name
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'ordered_tool_unique_name_by_workspace'
            ) 
            ALTER TABLE claro_ordered_tool 
            DROP CONSTRAINT ordered_tool_unique_name_by_workspace ELSE 
            DROP INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD CONSTRAINT FK_6CF1320E84A0A3ED FOREIGN KEY (content_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E84A0A3ED ON claro_ordered_tool (content_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD name NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP COLUMN content_id
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP CONSTRAINT FK_6CF1320E84A0A3ED
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_6CF1320E84A0A3ED'
            ) 
            ALTER TABLE claro_ordered_tool 
            DROP CONSTRAINT IDX_6CF1320E84A0A3ED ELSE 
            DROP INDEX IDX_6CF1320E84A0A3ED ON claro_ordered_tool
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name) 
            WHERE workspace_id IS NOT NULL 
            AND name IS NOT NULL
        ");
    }
}