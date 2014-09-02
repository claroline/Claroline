<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

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
                PRIMARY KEY (user_id, model_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_5318388FA76ED395 ON claro_workspace_model_user (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5318388F7975B7E7 ON claro_workspace_model_user (model_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_group (
                group_id INT NOT NULL, 
                model_id INT NOT NULL, 
                PRIMARY KEY (group_id, model_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1F19A8AEFE54D947 ON claro_workspace_model_group (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1F19A8AE7975B7E7 ON claro_workspace_model_group (model_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model (
                id INT IDENTITY NOT NULL, 
                workspace_id INT, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_536FFC4C82D40A1F ON claro_workspace_model (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_home_tab (
                hometab_id INT NOT NULL, 
                model_id INT NOT NULL, 
                PRIMARY KEY (hometab_id, model_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A8E0CB1BCCE862F ON claro_workspace_model_home_tab (hometab_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A8E0CB1B7975B7E7 ON claro_workspace_model_home_tab (model_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_resource (
                id INT IDENTITY NOT NULL, 
                model_id INT NOT NULL, 
                isCopy BIT NOT NULL, 
                resourceNode_id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F5D70635B87FAB32 ON claro_workspace_model_resource (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F5D706357975B7E7 ON claro_workspace_model_resource (model_id)
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
            DROP CONSTRAINT FK_5318388F7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_group 
            DROP CONSTRAINT FK_1F19A8AE7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_home_tab 
            DROP CONSTRAINT FK_A8E0CB1B7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_model_resource 
            DROP CONSTRAINT FK_F5D706357975B7E7
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