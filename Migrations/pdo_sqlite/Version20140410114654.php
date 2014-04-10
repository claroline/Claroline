<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/10 11:46:55
 */
class Version20140410114654 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_admin_tools (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C10C14EC5E237E06 ON claro_admin_tools (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_C10C14ECEC942BCF ON claro_admin_tools (plugin_id)
        ");
        $this->addSql("
            CREATE TABLE claro_admin_tool_role (
                tool_id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                PRIMARY KEY(tool_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_940800698F7B22CC ON claro_admin_tool_role (tool_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_94080069D60322AC ON claro_admin_tool_role (role_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_admin_tools
        ");
        $this->addSql("
            DROP TABLE claro_admin_tool_role
        ");
    }
}