<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/10 01:29:34
 */
class Version20130910132929 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD restrictions VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_copy
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_create
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_read
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN ws_tool_read
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_export
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_update
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_update_rename
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_child_update
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_delete
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN resource_move
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN ws_role_subscribe_user
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN ws_role_subscribe_group
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN ws_role_unsubscribe_user
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN ws_role_unsubscribe_group
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN ws_role_change_right
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN ws_role_create
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN ws_role_delete
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN ws_role_update
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_copy BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_create BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_shortcut BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_read BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_tool_read BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_export BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_update BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_update_rename BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_child_update BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_delete BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_move BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_subscribe_user BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_subscribe_group BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_unsubscribe_user BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_unsubscribe_group BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_change_right BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_create BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_delete BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD ws_role_update BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP COLUMN restrictions
        ");
    }
}