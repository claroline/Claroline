<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/24 01:32:23
 */
class Version20150224133221 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_plugin 
            DROP COLUMN icon
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE claro_plugin')
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP COLUMN icon
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE claro_widget')
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_plugin 
            ADD COLUMN icon VARCHAR(255) NOT NULL WITH DEFAULT
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD COLUMN icon VARCHAR(255) NOT NULL WITH DEFAULT
        ");
    }
}