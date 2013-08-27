<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/27 11:29:48
 */
class Version20130827112946 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_home_tab_main_config (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                workspace_id INT, 
                allow_desktop_tab_creation BIT NOT NULL, 
                allow_workspace_tab_creation BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C749B4E7A76ED395 ON claro_home_tab_main_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C749B4E782D40A1F ON claro_home_tab_main_config (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_main_config_unique_user_workspace ON claro_home_tab_main_config (user_id, workspace_id) 
            WHERE user_id IS NOT NULL 
            AND workspace_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab_config (
                id INT IDENTITY NOT NULL, 
                home_tab_id INT NOT NULL, 
                user_id INT, 
                workspace_id INT, 
                is_visible BIT NOT NULL, 
                is_locked BIT NOT NULL, 
                tab_order INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F530F6BE7D08FA9E ON claro_home_tab_config (home_tab_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F530F6BEA76ED395 ON claro_home_tab_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F530F6BE82D40A1F ON claro_home_tab_config (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user_workspace ON claro_home_tab_config (
                home_tab_id, user_id, workspace_id
            ) 
            WHERE home_tab_id IS NOT NULL 
            AND user_id IS NOT NULL 
            AND workspace_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_main_config 
            ADD CONSTRAINT FK_C749B4E7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_main_config 
            ADD CONSTRAINT FK_C749B4E782D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BE7D08FA9E FOREIGN KEY (home_tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BE82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'home_tab_unique_name_user_workspace'
            ) 
            ALTER TABLE claro_home_tab 
            DROP CONSTRAINT home_tab_unique_name_user_workspace ELSE 
            DROP INDEX home_tab_unique_name_user_workspace ON claro_home_tab
        ");
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_home_tab_main_config
        ");
        $this->addSql("
            DROP TABLE claro_home_tab_config
        ");
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
            CREATE UNIQUE INDEX home_tab_unique_name_user_workspace ON claro_home_tab (name, user_id, workspace_id) 
            WHERE name IS NOT NULL 
            AND user_id IS NOT NULL 
            AND workspace_id IS NOT NULL
        ");
    }
}