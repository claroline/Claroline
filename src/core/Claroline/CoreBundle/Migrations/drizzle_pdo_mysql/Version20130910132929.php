<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
            ADD restrictions TEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', 
            DROP resource_copy, 
            DROP resource_create, 
            DROP resource_shortcut, 
            DROP resource_read, 
            DROP ws_tool_read, 
            DROP resource_export, 
            DROP resource_update, 
            DROP resource_update_rename, 
            DROP resource_child_update, 
            DROP resource_delete, 
            DROP resource_move, 
            DROP ws_role_subscribe_user, 
            DROP ws_role_subscribe_group, 
            DROP ws_role_unsubscribe_user, 
            DROP ws_role_unsubscribe_group, 
            DROP ws_role_change_right, 
            DROP ws_role_create, 
            DROP ws_role_delete, 
            DROP ws_role_update
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD resource_copy BOOLEAN NOT NULL, 
            ADD resource_create BOOLEAN NOT NULL, 
            ADD resource_shortcut BOOLEAN NOT NULL, 
            ADD resource_read BOOLEAN NOT NULL, 
            ADD ws_tool_read BOOLEAN NOT NULL, 
            ADD resource_export BOOLEAN NOT NULL, 
            ADD resource_update BOOLEAN NOT NULL, 
            ADD resource_update_rename BOOLEAN NOT NULL, 
            ADD resource_child_update BOOLEAN NOT NULL, 
            ADD resource_delete BOOLEAN NOT NULL, 
            ADD resource_move BOOLEAN NOT NULL, 
            ADD ws_role_subscribe_user BOOLEAN NOT NULL, 
            ADD ws_role_subscribe_group BOOLEAN NOT NULL, 
            ADD ws_role_unsubscribe_user BOOLEAN NOT NULL, 
            ADD ws_role_unsubscribe_group BOOLEAN NOT NULL, 
            ADD ws_role_change_right BOOLEAN NOT NULL, 
            ADD ws_role_create BOOLEAN NOT NULL, 
            ADD ws_role_delete BOOLEAN NOT NULL, 
            ADD ws_role_update BOOLEAN NOT NULL, 
            DROP restrictions
        ");
    }
}