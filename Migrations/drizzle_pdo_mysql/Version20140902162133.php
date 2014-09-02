<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/02 04:21:35
 */
class Version20140902162133 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_model_user (
                user_id INT NOT NULL, 
                model_id INT NOT NULL, 
                PRIMARY KEY(user_id, model_id), 
                INDEX IDX_5318388FA76ED395 (user_id), 
                INDEX IDX_5318388F7975B7E7 (model_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_group (
                group_id INT NOT NULL, 
                model_id INT NOT NULL, 
                PRIMARY KEY(group_id, model_id), 
                INDEX IDX_1F19A8AEFE54D947 (group_id), 
                INDEX IDX_1F19A8AE7975B7E7 (model_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_536FFC4C82D40A1F (workspace_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_home_tab (
                hometab_id INT NOT NULL, 
                model_id INT NOT NULL, 
                PRIMARY KEY(hometab_id, model_id), 
                INDEX IDX_A8E0CB1BCCE862F (hometab_id), 
                INDEX IDX_A8E0CB1B7975B7E7 (model_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                model_id INT NOT NULL, 
                isCopy BOOLEAN NOT NULL, 
                resourceNode_id INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_F5D70635B87FAB32 (resourceNode_id), 
                INDEX IDX_F5D706357975B7E7 (model_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_user 
            ADD CONSTRAINT FK_5318388FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_user 
            ADD CONSTRAINT FK_5318388F7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_group 
            ADD CONSTRAINT FK_1F19A8AEFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_group 
            ADD CONSTRAINT FK_1F19A8AE7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model 
            ADD CONSTRAINT FK_536FFC4C82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_home_tab 
            ADD CONSTRAINT FK_A8E0CB1BCCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_home_tab 
            ADD CONSTRAINT FK_A8E0CB1B7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_resource 
            ADD CONSTRAINT FK_F5D70635B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_resource 
            ADD CONSTRAINT FK_F5D706357975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace_model_user 
            DROP FOREIGN KEY FK_5318388F7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_group 
            DROP FOREIGN KEY FK_1F19A8AE7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_home_tab 
            DROP FOREIGN KEY FK_A8E0CB1B7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_resource 
            DROP FOREIGN KEY FK_F5D706357975B7E7
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model_user
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model_group
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model_home_tab
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model_resource
        ");
    }
}