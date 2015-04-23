<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/20 05:19:23
 */
class Version20150420171923 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_user_type
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_ws_type
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_name_by_workspace
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
                content_id INTEGER NOT NULL, 
                display_order INTEGER NOT NULL, 
                is_visible_in_desktop BOOLEAN NOT NULL, 
                ordered_tool_type INTEGER NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6CF1320E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320E8F7B22CC FOREIGN KEY (tool_id) 
                REFERENCES claro_tools (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320E84A0A3ED FOREIGN KEY (content_id) 
                REFERENCES claro_content (id) 
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
            CREATE INDEX IDX_6CF1320E84A0A3ED ON claro_ordered_tool (content_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_6CF1320E82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E8F7B22CC
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E84A0A3ED
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
    }
}