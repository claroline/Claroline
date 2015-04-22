<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/22 10:38:44
 */
class Version20150422103843 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_ordered_tool_translation (
                id INTEGER NOT NULL, 
                locale VARCHAR(8) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                field VARCHAR(32) NOT NULL, 
                foreign_key VARCHAR(64) NOT NULL, 
                content CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX tool_ordered_translation_idx ON claro_ordered_tool_translation (
                locale, object_class, field, foreign_key
            )
        ");
        $this->addSql("
            CREATE TABLE claro_tool_translation (
                id INTEGER NOT NULL, 
                locale VARCHAR(8) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                field VARCHAR(32) NOT NULL, 
                foreign_key VARCHAR(64) NOT NULL, 
                content CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX tool_translation_idx ON claro_tool_translation (
                locale, object_class, field, foreign_key
            )
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_name_by_workspace
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_user_type
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_ws_type
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E8F7B22CC
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320EA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_ordered_tool AS 
            SELECT id, 
            workspace_id, 
            tool_id, 
            user_id, 
            display_order, 
            is_visible_in_desktop, 
            ordered_tool_type, 
            is_locked 
            FROM claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE claro_ordered_tool
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                tool_id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                display_order INTEGER NOT NULL, 
                is_visible_in_desktop BOOLEAN NOT NULL, 
                ordered_tool_type INTEGER NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                displayedName VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6CF1320E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320E8F7B22CC FOREIGN KEY (tool_id) 
                REFERENCES claro_tools (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_ordered_tool (
                id, workspace_id, tool_id, user_id, 
                display_order, is_visible_in_desktop, 
                ordered_tool_type, is_locked
            ) 
            SELECT id, 
            workspace_id, 
            tool_id, 
            user_id, 
            display_order, 
            is_visible_in_desktop, 
            ordered_tool_type, 
            is_locked 
            FROM __temp__claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE __temp__claro_ordered_tool
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool (
                tool_id, user_id, ordered_tool_type
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool (
                tool_id, workspace_id, ordered_tool_type
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E82D40A1F ON claro_ordered_tool (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E8F7B22CC ON claro_ordered_tool (tool_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320EA76ED395 ON claro_ordered_tool (user_id)
        ");
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
            display_name, 
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
                displayedName VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_60F90965EC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_tools (
                id, plugin_id, name, displayedName, 
                class, is_workspace_required, is_desktop_required, 
                is_displayable_in_workspace, is_displayable_in_desktop, 
                is_exportable, is_configurable_in_workspace, 
                is_configurable_in_desktop, is_locked_for_admin, 
                is_anonymous_excluded
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            display_name, 
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

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_ordered_tool_translation
        ");
        $this->addSql("
            DROP TABLE claro_tool_translation
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E8F7B22CC
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320EA76ED395
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_user_type
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_ws_type
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_ordered_tool AS 
            SELECT id, 
            workspace_id, 
            tool_id, 
            user_id, 
            display_order, 
            is_visible_in_desktop, 
            ordered_tool_type, 
            is_locked 
            FROM claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE claro_ordered_tool
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                tool_id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                display_order INTEGER NOT NULL, 
                is_visible_in_desktop BOOLEAN NOT NULL, 
                ordered_tool_type INTEGER NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6CF1320E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320E8F7B22CC FOREIGN KEY (tool_id) 
                REFERENCES claro_tools (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_ordered_tool (
                id, workspace_id, tool_id, user_id, 
                display_order, is_visible_in_desktop, 
                ordered_tool_type, is_locked
            ) 
            SELECT id, 
            workspace_id, 
            tool_id, 
            user_id, 
            display_order, 
            is_visible_in_desktop, 
            ordered_tool_type, 
            is_locked 
            FROM __temp__claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE __temp__claro_ordered_tool
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E82D40A1F ON claro_ordered_tool (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E8F7B22CC ON claro_ordered_tool (tool_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320EA76ED395 ON claro_ordered_tool (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool (
                tool_id, user_id, ordered_tool_type
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool (
                tool_id, workspace_id, ordered_tool_type
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name)
        ");
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
            displayedName, 
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
                id, plugin_id, name, display_name, 
                class, is_workspace_required, is_desktop_required, 
                is_displayable_in_workspace, is_displayable_in_desktop, 
                is_exportable, is_configurable_in_workspace, 
                is_configurable_in_desktop, is_locked_for_admin, 
                is_anonymous_excluded
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            displayedName, 
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