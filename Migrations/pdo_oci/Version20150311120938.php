<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/11 12:09:40
 */
class Version20150311120938 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD (details CLOB DEFAULT NULL NULL)
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_widget_home_tab_config.details IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD (
                default_width NUMBER(10) DEFAULT 4 NOT NULL, 
                default_height NUMBER(10) DEFAULT 3 NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP (default_width, default_height)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP (details)
        ");
    }
}