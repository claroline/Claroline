<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

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
            ADD restrictions LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', 
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
            ADD resource_copy TINYINT(1) NOT NULL, 
            ADD resource_create TINYINT(1) NOT NULL, 
            ADD resource_shortcut TINYINT(1) NOT NULL, 
            ADD resource_read TINYINT(1) NOT NULL, 
            ADD ws_tool_read TINYINT(1) NOT NULL, 
            ADD resource_export TINYINT(1) NOT NULL, 
            ADD resource_update TINYINT(1) NOT NULL, 
            ADD resource_update_rename TINYINT(1) NOT NULL, 
            ADD resource_child_update TINYINT(1) NOT NULL, 
            ADD resource_delete TINYINT(1) NOT NULL, 
            ADD resource_move TINYINT(1) NOT NULL, 
            ADD ws_role_subscribe_user TINYINT(1) NOT NULL, 
            ADD ws_role_subscribe_group TINYINT(1) NOT NULL, 
            ADD ws_role_unsubscribe_user TINYINT(1) NOT NULL, 
            ADD ws_role_unsubscribe_group TINYINT(1) NOT NULL, 
            ADD ws_role_change_right TINYINT(1) NOT NULL, 
            ADD ws_role_create TINYINT(1) NOT NULL, 
            ADD ws_role_delete TINYINT(1) NOT NULL, 
            ADD ws_role_update TINYINT(1) NOT NULL, 
            DROP restrictions
        ");
    }
}