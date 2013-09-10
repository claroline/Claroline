<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/10 01:29:33
 */
class Version20130910132929 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD (restrictions CLOB DEFAULT NULL)
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP (
                resource_copy, resource_create, resource_shortcut, 
                resource_read, ws_tool_read, resource_export, 
                resource_update, resource_update_rename, 
                resource_child_update, resource_delete, 
                resource_move, ws_role_subscribe_user, 
                ws_role_subscribe_group, ws_role_unsubscribe_user, 
                ws_role_unsubscribe_group, ws_role_change_right, 
                ws_role_create, ws_role_delete, 
                ws_role_update
            )
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_log_workspace_widget_config.restrictions IS '(DC2Type:simple_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD (
                resource_copy NUMBER(1) NOT NULL, 
                resource_create NUMBER(1) NOT NULL, 
                resource_shortcut NUMBER(1) NOT NULL, 
                resource_read NUMBER(1) NOT NULL, 
                ws_tool_read NUMBER(1) NOT NULL, 
                resource_export NUMBER(1) NOT NULL, 
                resource_update NUMBER(1) NOT NULL, 
                resource_update_rename NUMBER(1) NOT NULL, 
                resource_child_update NUMBER(1) NOT NULL, 
                resource_delete NUMBER(1) NOT NULL, 
                resource_move NUMBER(1) NOT NULL, 
                ws_role_subscribe_user NUMBER(1) NOT NULL, 
                ws_role_subscribe_group NUMBER(1) NOT NULL, 
                ws_role_unsubscribe_user NUMBER(1) NOT NULL, 
                ws_role_unsubscribe_group NUMBER(1) NOT NULL, 
                ws_role_change_right NUMBER(1) NOT NULL, 
                ws_role_create NUMBER(1) NOT NULL, 
                ws_role_delete NUMBER(1) NOT NULL, 
                ws_role_update NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP (restrictions)
        ");
    }
}