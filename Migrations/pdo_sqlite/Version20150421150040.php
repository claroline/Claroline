<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/21 03:00:40
 */
class Version20150421150040 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_60F909655E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_60F90965EC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_tools AS 
            SELECT id, 
            plugin_id, 
            name, 
            class, 
            is_workspace_required, 
            is_desktop_required, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop, 
            is_exportable, 
            is_configurable_in_workspace, 
            is_configurable_in_desktop, 
            is_locked_for_admin, 
            is_anonymous_excluded 
            FROM claro_tools
        ");
        $this->addSql("
            DROP TABLE claro_tools
        ");
        $this->addSql("
            CREATE TABLE claro_tools (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                content_id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                is_workspace_required BOOLEAN NOT NULL, 
                is_desktop_required BOOLEAN NOT NULL, 
                is_displayable_in_workspace BOOLEAN NOT NULL, 
                is_displayable_in_desktop BOOLEAN NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                is_configurable_in_workspace BOOLEAN NOT NULL, 
                is_configurable_in_desktop BOOLEAN NOT NULL, 
                is_locked_for_admin BOOLEAN NOT NULL, 
                is_anonymous_excluded BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_60F90965EC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_60F9096584A0A3ED FOREIGN KEY (content_id) 
                REFERENCES claro_content (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_tools (
                id, plugin_id, name, class, is_workspace_required, 
                is_desktop_required, is_displayable_in_workspace, 
                is_displayable_in_desktop, is_exportable, 
                is_configurable_in_workspace, is_configurable_in_desktop, 
                is_locked_for_admin, is_anonymous_excluded
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            class, 
            is_workspace_required, 
            is_desktop_required, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop, 
            is_exportable, 
            is_configurable_in_workspace, 
            is_configurable_in_desktop, 
            is_locked_for_admin, 
            is_anonymous_excluded 
            FROM __temp__claro_tools
        ");
        $this->addSql("
            DROP TABLE __temp__claro_tools
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_60F909655E237E06 ON claro_tools (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_60F90965EC942BCF ON claro_tools (plugin_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_60F9096584A0A3ED ON claro_tools (content_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_60F909655E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_60F9096584A0A3ED
        ");
        $this->addSql("
            DROP INDEX IDX_60F90965EC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_tools AS 
            SELECT id, 
            plugin_id, 
            name, 
            class, 
            is_workspace_required, 
            is_desktop_required, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop, 
            is_exportable, 
            is_configurable_in_workspace, 
            is_configurable_in_desktop, 
            is_locked_for_admin, 
            is_anonymous_excluded 
            FROM claro_tools
        ");
        $this->addSql("
            DROP TABLE claro_tools
        ");
        $this->addSql("
            CREATE TABLE claro_tools (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                is_workspace_required BOOLEAN NOT NULL, 
                is_desktop_required BOOLEAN NOT NULL, 
                is_displayable_in_workspace BOOLEAN NOT NULL, 
                is_displayable_in_desktop BOOLEAN NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                is_configurable_in_workspace BOOLEAN NOT NULL, 
                is_configurable_in_desktop BOOLEAN NOT NULL, 
                is_locked_for_admin BOOLEAN NOT NULL, 
                is_anonymous_excluded BOOLEAN NOT NULL, 
                display_name VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_60F90965EC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_tools (
                id, plugin_id, name, class, is_workspace_required, 
                is_desktop_required, is_displayable_in_workspace, 
                is_displayable_in_desktop, is_exportable, 
                is_configurable_in_workspace, is_configurable_in_desktop, 
                is_locked_for_admin, is_anonymous_excluded
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            class, 
            is_workspace_required, 
            is_desktop_required, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop, 
            is_exportable, 
            is_configurable_in_workspace, 
            is_configurable_in_desktop, 
            is_locked_for_admin, 
            is_anonymous_excluded 
            FROM __temp__claro_tools
        ");
        $this->addSql("
            DROP TABLE __temp__claro_tools
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_60F909655E237E06 ON claro_tools (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_60F90965EC942BCF ON claro_tools (plugin_id)
        ");
    }
}