<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/27 11:52:56
 */
class Version20130827115255 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'home_tab_unique_name_user'
            ) 
            ALTER TABLE claro_home_tab 
            DROP CONSTRAINT home_tab_unique_name_user ELSE 
            DROP INDEX home_tab_unique_name_user ON claro_home_tab
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'home_tab_unique_name_workspace'
            ) 
            ALTER TABLE claro_home_tab 
            DROP CONSTRAINT home_tab_unique_name_workspace ELSE 
            DROP INDEX home_tab_unique_name_workspace ON claro_home_tab
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'home_tab_config_unique_home_tab_user_workspace'
            ) 
            ALTER TABLE claro_home_tab_config 
            DROP CONSTRAINT home_tab_config_unique_home_tab_user_workspace ELSE 
            DROP INDEX home_tab_config_unique_home_tab_user_workspace ON claro_home_tab_config
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user ON claro_home_tab_config (home_tab_id, user_id) 
            WHERE home_tab_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_workspace ON claro_home_tab_config (home_tab_id, workspace_id) 
            WHERE home_tab_id IS NOT NULL 
            AND workspace_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_user ON claro_home_tab (name, user_id) 
            WHERE name IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_workspace ON claro_home_tab (name, workspace_id) 
            WHERE name IS NOT NULL 
            AND workspace_id IS NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'home_tab_config_unique_home_tab_user'
            ) 
            ALTER TABLE claro_home_tab_config 
            DROP CONSTRAINT home_tab_config_unique_home_tab_user ELSE 
            DROP INDEX home_tab_config_unique_home_tab_user ON claro_home_tab_config
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'home_tab_config_unique_home_tab_workspace'
            ) 
            ALTER TABLE claro_home_tab_config 
            DROP CONSTRAINT home_tab_config_unique_home_tab_workspace ELSE 
            DROP INDEX home_tab_config_unique_home_tab_workspace ON claro_home_tab_config
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user_workspace ON claro_home_tab_config (
                home_tab_id, user_id, workspace_id
            ) 
            WHERE home_tab_id IS NOT NULL 
            AND user_id IS NOT NULL 
            AND workspace_id IS NOT NULL
        ");
    }
}