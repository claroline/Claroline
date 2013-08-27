<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

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
            DROP INDEX home_tab_unique_name_user
        ");
        $this->addSql("
            DROP INDEX home_tab_unique_name_workspace
        ");
        $this->addSql("
            DROP INDEX IDX_A9744CCEA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_A9744CCE82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_home_tab AS 
            SELECT id, 
            workspace_id, 
            user_id, 
            name, 
            type, 
            tab_order 
            FROM claro_home_tab
        ");
        $this->addSql("
            DROP TABLE claro_home_tab
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                tab_order INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A9744CCE82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A9744CCEA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_home_tab (
                id, workspace_id, user_id, name, type, 
                tab_order
            ) 
            SELECT id, 
            workspace_id, 
            user_id, 
            name, 
            type, 
            tab_order 
            FROM __temp__claro_home_tab
        ");
        $this->addSql("
            DROP TABLE __temp__claro_home_tab
        ");
        $this->addSql("
            CREATE INDEX IDX_A9744CCEA76ED395 ON claro_home_tab (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A9744CCE82D40A1F ON claro_home_tab (workspace_id)
        ");
        $this->addSql("
            DROP INDEX home_tab_config_unique_home_tab_user_workspace
        ");
        $this->addSql("
            DROP INDEX IDX_F530F6BE7D08FA9E
        ");
        $this->addSql("
            DROP INDEX IDX_F530F6BEA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F530F6BE82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_home_tab_config AS 
            SELECT id, 
            workspace_id, 
            home_tab_id, 
            user_id, 
            is_visible, 
            is_locked, 
            tab_order 
            FROM claro_home_tab_config
        ");
        $this->addSql("
            DROP TABLE claro_home_tab_config
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                home_tab_id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                is_visible BOOLEAN NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                tab_order INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F530F6BE82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F530F6BE7D08FA9E FOREIGN KEY (home_tab_id) 
                REFERENCES claro_home_tab (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F530F6BEA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_home_tab_config (
                id, workspace_id, home_tab_id, user_id, 
                is_visible, is_locked, tab_order
            ) 
            SELECT id, 
            workspace_id, 
            home_tab_id, 
            user_id, 
            is_visible, 
            is_locked, 
            tab_order 
            FROM __temp__claro_home_tab_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_home_tab_config
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
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user ON claro_home_tab_config (home_tab_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_workspace ON claro_home_tab_config (home_tab_id, workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_A9744CCEA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_A9744CCE82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_home_tab AS 
            SELECT id, 
            user_id, 
            workspace_id, 
            name, 
            type, 
            tab_order 
            FROM claro_home_tab
        ");
        $this->addSql("
            DROP TABLE claro_home_tab
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                tab_order INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A9744CCEA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A9744CCE82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_home_tab (
                id, user_id, workspace_id, name, type, 
                tab_order
            ) 
            SELECT id, 
            user_id, 
            workspace_id, 
            name, 
            type, 
            tab_order 
            FROM __temp__claro_home_tab
        ");
        $this->addSql("
            DROP TABLE __temp__claro_home_tab
        ");
        $this->addSql("
            CREATE INDEX IDX_A9744CCEA76ED395 ON claro_home_tab (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A9744CCE82D40A1F ON claro_home_tab (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_user ON claro_home_tab (name, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_workspace ON claro_home_tab (name, workspace_id)
        ");
        $this->addSql("
            DROP INDEX IDX_F530F6BE7D08FA9E
        ");
        $this->addSql("
            DROP INDEX IDX_F530F6BEA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F530F6BE82D40A1F
        ");
        $this->addSql("
            DROP INDEX home_tab_config_unique_home_tab_user
        ");
        $this->addSql("
            DROP INDEX home_tab_config_unique_home_tab_workspace
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_home_tab_config AS 
            SELECT id, 
            home_tab_id, 
            user_id, 
            workspace_id, 
            is_visible, 
            is_locked, 
            tab_order 
            FROM claro_home_tab_config
        ");
        $this->addSql("
            DROP TABLE claro_home_tab_config
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab_config (
                id INTEGER NOT NULL, 
                home_tab_id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                is_visible BOOLEAN NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                tab_order INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F530F6BE7D08FA9E FOREIGN KEY (home_tab_id) 
                REFERENCES claro_home_tab (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F530F6BEA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F530F6BE82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_home_tab_config (
                id, home_tab_id, user_id, workspace_id, 
                is_visible, is_locked, tab_order
            ) 
            SELECT id, 
            home_tab_id, 
            user_id, 
            workspace_id, 
            is_visible, 
            is_locked, 
            tab_order 
            FROM __temp__claro_home_tab_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_home_tab_config
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
        ");
    }
}