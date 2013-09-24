<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/24 12:00:13
 */
class Version20130924120013 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_widget_instance (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                widget_id INTEGER NOT NULL, 
                is_admin BOOLEAN NOT NULL, 
                is_°desktop BOOLEAN NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A38582D40A1F ON claro_widget_instance (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A385A76ED395 ON claro_widget_instance (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A385FBE885E2 ON claro_widget_instance (widget_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD COLUMN is_displayable_in_workspace BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD COLUMN is_displayable_in_desktop BOOLEAN NOT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_11925ED382D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__simple_text_workspace_widget_config AS 
            SELECT id, 
            workspace_id, 
            content 
            FROM simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE simple_text_workspace_widget_config (
                id INTEGER NOT NULL, 
                content CLOB NOT NULL, 
                displayConfig_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_11925ED3EF00646E FOREIGN KEY (displayConfig_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO simple_text_workspace_widget_config (id, displayConfig_id, content) 
            SELECT id, 
            workspace_id, 
            content 
            FROM __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED3EF00646E ON simple_text_workspace_widget_config (displayConfig_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_widget_instance
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
            icon, 
            is_exportable 
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
                icon VARCHAR(255) NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_76CA6C4FEC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget (
                id, plugin_id, name, is_configurable, 
                icon, is_exportable
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            icon, 
            is_exportable 
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
        $this->addSql("
            DROP INDEX IDX_11925ED3EF00646E
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__simple_text_workspace_widget_config AS 
            SELECT id, 
            content, 
            displayConfig_id 
            FROM simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE simple_text_workspace_widget_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                content CLOB NOT NULL, 
                is_default BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_11925ED3EF00646E FOREIGN KEY (workspace_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO simple_text_workspace_widget_config (id, content, workspace_id) 
            SELECT id, 
            content, 
            displayConfig_id 
            FROM __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED382D40A1F ON simple_text_workspace_widget_config (workspace_id)
        ");
    }
}