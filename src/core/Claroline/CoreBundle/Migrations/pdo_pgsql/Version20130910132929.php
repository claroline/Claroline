<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

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
            ALTER TABLE claro_log_workspace_widget_config 
            ADD restrictions TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_copy
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_create
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_read
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP ws_tool_read
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_export
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_update
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_update_rename
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_child_update
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_delete
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP resource_move
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP ws_role_subscribe_user
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP ws_role_subscribe_group
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP ws_role_unsubscribe_user
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP ws_role_unsubscribe_group
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP ws_role_change_right
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP ws_role_create
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP ws_role_delete
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP ws_role_update
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_log_workspace_widget_config.restrictions IS '(DC2Type:simple_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_copy BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_create BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_shortcut BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_read BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_tool_read BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_export BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_update BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_update_rename BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_child_update BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_delete BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_move BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_subscribe_user BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_subscribe_group BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_unsubscribe_user BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_unsubscribe_group BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_change_right BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_create BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_delete BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_update BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP restrictions
        ");
    }
}