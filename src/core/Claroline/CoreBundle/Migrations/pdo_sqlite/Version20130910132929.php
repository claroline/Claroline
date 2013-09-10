<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/10 01:29:32
 */
class Version20130910132929 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_D301C70782D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log_workspace_widget_config AS 
            SELECT id, 
            workspace_id, 
            is_default, 
            amount 
            FROM claro_log_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE claro_log_workspace_widget_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                amount INTEGER NOT NULL, 
                restrictions CLOB DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D301C70782D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log_workspace_widget_config (
                id, workspace_id, is_default, amount
            ) 
            SELECT id, 
            workspace_id, 
            is_default, 
            amount 
            FROM __temp__claro_log_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log_workspace_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_D301C70782D40A1F ON claro_log_workspace_widget_config (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_D301C70782D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log_workspace_widget_config AS 
            SELECT id, 
            workspace_id, 
            is_default, 
            amount 
            FROM claro_log_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE claro_log_workspace_widget_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                amount INTEGER NOT NULL, 
                resource_copy BOOLEAN NOT NULL, 
                resource_create BOOLEAN NOT NULL, 
                resource_shortcut BOOLEAN NOT NULL, 
                resource_read BOOLEAN NOT NULL, 
                ws_tool_read BOOLEAN NOT NULL, 
                resource_export BOOLEAN NOT NULL, 
                resource_update BOOLEAN NOT NULL, 
                resource_update_rename BOOLEAN NOT NULL, 
                resource_child_update BOOLEAN NOT NULL, 
                resource_delete BOOLEAN NOT NULL, 
                resource_move BOOLEAN NOT NULL, 
                ws_role_subscribe_user BOOLEAN NOT NULL, 
                ws_role_subscribe_group BOOLEAN NOT NULL, 
                ws_role_unsubscribe_user BOOLEAN NOT NULL, 
                ws_role_unsubscribe_group BOOLEAN NOT NULL, 
                ws_role_change_right BOOLEAN NOT NULL, 
                ws_role_create BOOLEAN NOT NULL, 
                ws_role_delete BOOLEAN NOT NULL, 
                ws_role_update BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D301C70782D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log_workspace_widget_config (
                id, workspace_id, is_default, amount
            ) 
            SELECT id, 
            workspace_id, 
            is_default, 
            amount 
            FROM __temp__claro_log_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log_workspace_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_D301C70782D40A1F ON claro_log_workspace_widget_config (workspace_id)
        ");
    }
}