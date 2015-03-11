<?php

namespace Claroline\CoreBundle\Migrations\sqlanywhere;

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
            ADD details TEXT DEFAULT NULL
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_widget_home_tab_config.details IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD default_width INT DEFAULT 4 NOT NULL, 
            ADD default_height INT DEFAULT 3 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP default_width, 
            DROP default_height
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP details
        ");
    }
}