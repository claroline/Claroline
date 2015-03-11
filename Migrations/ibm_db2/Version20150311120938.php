<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/11 12:09:41
 */
class Version20150311120938 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD COLUMN details CLOB(1M) DEFAULT NULL
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_widget_home_tab_config.details IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD COLUMN default_width INTEGER DEFAULT 4 NOT NULL 
            ADD COLUMN default_height INTEGER DEFAULT 3 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP COLUMN default_width 
            DROP COLUMN default_height
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE claro_widget')
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP COLUMN details
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD (
                'REORG TABLE claro_widget_home_tab_config'
            )
        ");
    }
}