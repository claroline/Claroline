<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/22 10:38:44
 */
class Version20150422103843 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_ordered_tool_translation (
                id INT IDENTITY NOT NULL, 
                locale NVARCHAR(8) NOT NULL, 
                object_class NVARCHAR(255) NOT NULL, 
                field NVARCHAR(32) NOT NULL, 
                foreign_key NVARCHAR(64) NOT NULL, 
                content VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX tool_ordered_translation_idx ON claro_ordered_tool_translation (
                locale, object_class, field, foreign_key
            )
        ");
        $this->addSql("
            CREATE TABLE claro_tool_translation (
                id INT IDENTITY NOT NULL, 
                locale NVARCHAR(8) NOT NULL, 
                object_class NVARCHAR(255) NOT NULL, 
                field NVARCHAR(32) NOT NULL, 
                foreign_key NVARCHAR(64) NOT NULL, 
                content VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX tool_translation_idx ON claro_tool_translation (
                locale, object_class, field, foreign_key
            )
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD displayedName NVARCHAR(255)
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
            sp_RENAME 'claro_tools.display_name', 
            'displayedName', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_tools ALTER COLUMN displayedName NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_ordered_tool_translation
        ");
        $this->addSql("
            DROP TABLE claro_tool_translation
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD name NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP COLUMN displayedName
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name) 
            WHERE workspace_id IS NOT NULL 
            AND name IS NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'claro_tools.displayedname', 
            'display_name', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_tools ALTER COLUMN display_name NVARCHAR(255)
        ");
    }
}