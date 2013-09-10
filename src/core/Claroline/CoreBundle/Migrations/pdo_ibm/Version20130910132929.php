<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

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
            ADD COLUMN restrictions CLOB(1M) DEFAULT NULL 
            DROP COLUMN resource_copy 
            DROP COLUMN resource_create 
            DROP COLUMN resource_shortcut 
            DROP COLUMN resource_read 
            DROP COLUMN ws_tool_read 
            DROP COLUMN resource_export 
            DROP COLUMN resource_update 
            DROP COLUMN resource_update_rename 
            DROP COLUMN resource_child_update 
            DROP COLUMN resource_delete 
            DROP COLUMN resource_move 
            DROP COLUMN ws_role_subscribe_user 
            DROP COLUMN ws_role_subscribe_group 
            DROP COLUMN ws_role_unsubscribe_user 
            DROP COLUMN ws_role_unsubscribe_group 
            DROP COLUMN ws_role_change_right 
            DROP COLUMN ws_role_create 
            DROP COLUMN ws_role_delete 
            DROP COLUMN ws_role_update
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD COLUMN resource_copy SMALLINT NOT NULL 
            ADD COLUMN resource_create SMALLINT NOT NULL 
            ADD COLUMN resource_shortcut SMALLINT NOT NULL 
            ADD COLUMN resource_read SMALLINT NOT NULL 
            ADD COLUMN ws_tool_read SMALLINT NOT NULL 
            ADD COLUMN resource_export SMALLINT NOT NULL 
            ADD COLUMN resource_update SMALLINT NOT NULL 
            ADD COLUMN resource_update_rename SMALLINT NOT NULL 
            ADD COLUMN resource_child_update SMALLINT NOT NULL 
            ADD COLUMN resource_delete SMALLINT NOT NULL 
            ADD COLUMN resource_move SMALLINT NOT NULL 
            ADD COLUMN ws_role_subscribe_user SMALLINT NOT NULL 
            ADD COLUMN ws_role_subscribe_group SMALLINT NOT NULL 
            ADD COLUMN ws_role_unsubscribe_user SMALLINT NOT NULL 
            ADD COLUMN ws_role_unsubscribe_group SMALLINT NOT NULL 
            ADD COLUMN ws_role_change_right SMALLINT NOT NULL 
            ADD COLUMN ws_role_create SMALLINT NOT NULL 
            ADD COLUMN ws_role_delete SMALLINT NOT NULL 
            ADD COLUMN ws_role_update SMALLINT NOT NULL 
            DROP COLUMN restrictions
        ");
    }
}