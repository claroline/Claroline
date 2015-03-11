<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/11 02:40:11
 */
class Version20150311144010 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_widget_display_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                widget_instance_id INTEGER NOT NULL, 
                row_position INTEGER NOT NULL, 
                column_position INTEGER NOT NULL, 
                widget_width INTEGER DEFAULT 4 NOT NULL, 
                widget_height INTEGER DEFAULT 3 NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_EBBE497282D40A1F ON claro_widget_display_config (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EBBE4972A76ED395 ON claro_widget_display_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EBBE497244BF891 ON claro_widget_display_config (widget_instance_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_display_config_unique_user ON claro_widget_display_config (widget_instance_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_display_config_unique_workspace ON claro_widget_display_config (
                widget_instance_id, workspace_id
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD COLUMN default_width INTEGER DEFAULT 4 NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD COLUMN default_height INTEGER DEFAULT 3 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_widget_display_config
        ");
        $this->addSql("
            DROP INDEX UNIQ_76CA6C4F5E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_76CA6C4FEC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_widget AS 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            is_exportable, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop 
            FROM claro_widget
        ");
        $this->addSql("
            DROP TABLE claro_widget
        ");
        $this->addSql("
            CREATE TABLE claro_widget (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_configurable BOOLEAN NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                is_displayable_in_workspace BOOLEAN NOT NULL, 
                is_displayable_in_desktop BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_76CA6C4FEC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget (
                id, plugin_id, name, is_configurable, 
                is_exportable, is_displayable_in_workspace, 
                is_displayable_in_desktop
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            is_exportable, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop 
            FROM __temp__claro_widget
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_76CA6C4F5E237E06 ON claro_widget (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_76CA6C4FEC942BCF ON claro_widget (plugin_id)
        ");
    }
}