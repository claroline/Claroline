<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/06 11:24:57
 */
class Version20150306112455 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD COLUMN ordered_tool_type INTEGER NOT NULL WITH DEFAULT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP COLUMN ordered_tool_type
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD (
                'REORG TABLE claro_ordered_tool'
            )
        ");
    }
}