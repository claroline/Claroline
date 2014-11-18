<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/12 11:25:43
 */
class Version20141112112541 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_tool_rights (
                id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                ordered_tool_id INTEGER NOT NULL, 
                mask INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_EFEDEC7ED60322AC ON claro_tool_rights (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EFEDEC7EBAC1B1D7 ON claro_tool_rights (ordered_tool_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tool_rights_unique_ordered_tool_role ON claro_tool_rights (ordered_tool_id, role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_tool_mask_decoder (
                id INTEGER NOT NULL, 
                tool_id INTEGER NOT NULL, 
                value INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                granted_icon_class VARCHAR(255) NOT NULL, 
                denied_icon_class VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_323623448F7B22CC ON claro_tool_mask_decoder (tool_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tool_mask_decoder_unique_tool_and_name ON claro_tool_mask_decoder (tool_id, name)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_tool_rights
        ");
        $this->addSql("
            DROP TABLE claro_tool_mask_decoder
        ");
    }
}