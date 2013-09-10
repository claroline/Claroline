<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/05 11:27:40
 */
class Version20130905112737 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'widget_home_tab_unique_order'
            ) 
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT widget_home_tab_unique_order ELSE 
            DROP INDEX widget_home_tab_unique_order ON claro_widget_home_tab_config
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX widget_home_tab_unique_order ON claro_widget_home_tab_config (
                widget_id, home_tab_id, widget_order
            ) 
            WHERE widget_id IS NOT NULL 
            AND home_tab_id IS NOT NULL 
            AND widget_order IS NOT NULL
        ");
    }
}