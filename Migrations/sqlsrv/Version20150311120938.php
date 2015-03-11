<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

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
            ADD details VARCHAR(MAX)
        ");
        $this->addSql("
            EXEC sp_addextendedproperty N 'MS_Description', 
            N '(DC2Type:json_array)', 
            N 'SCHEMA', 
            dbo, 
            N 'TABLE', 
            claro_widget_home_tab_config, 
            N 'COLUMN', 
            details
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD default_width INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD CONSTRAINT DF_76CA6C4F_653C1121 DEFAULT 4 FOR default_width
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD default_height INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD CONSTRAINT DF_76CA6C4F_121CEE5C DEFAULT 3 FOR default_height
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP COLUMN default_width
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP COLUMN default_height
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP COLUMN details
        ");
    }
}