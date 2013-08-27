<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/27 11:29:47
 */
class Version20130827112946 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_home_tab_main_config (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                allow_desktop_tab_creation BOOLEAN NOT NULL, 
                allow_workspace_tab_creation BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_C749B4E7A76ED395 (user_id), 
                INDEX IDX_C749B4E782D40A1F (workspace_id), 
                UNIQUE INDEX home_tab_main_config_unique_user_workspace (user_id, workspace_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab_config (
                id INT AUTO_INCREMENT NOT NULL, 
                home_tab_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                is_visible BOOLEAN NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                tab_order INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_F530F6BE7D08FA9E (home_tab_id), 
                INDEX IDX_F530F6BEA76ED395 (user_id), 
                INDEX IDX_F530F6BE82D40A1F (workspace_id), 
                UNIQUE INDEX home_tab_config_unique_home_tab_user_workspace (
                    home_tab_id, user_id, workspace_id
                )
            )
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
            DROP INDEX home_tab_unique_name_user_workspace ON claro_home_tab
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_user ON claro_home_tab (name, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_workspace ON claro_home_tab (name, workspace_id)
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
            DROP INDEX home_tab_unique_name_user ON claro_home_tab
        ");
        $this->addSql("
            DROP INDEX home_tab_unique_name_workspace ON claro_home_tab
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_user_workspace ON claro_home_tab (name, user_id, workspace_id)
        ");
    }
}