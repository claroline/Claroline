<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/06 02:57:57
 */
class Version20140806145755 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_model (
                user_id INT NOT NULL, 
                model_id INT NOT NULL, 
                PRIMARY KEY(user_id, model_id), 
                INDEX IDX_57DE02DBA76ED395 (user_id), 
                INDEX IDX_57DE02DB7975B7E7 (model_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_group_model (
                group_id INT NOT NULL, 
                model_id INT NOT NULL, 
                PRIMARY KEY(group_id, model_id), 
                INDEX IDX_C6568DFFE54D947 (group_id), 
                INDEX IDX_C6568DF7975B7E7 (model_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_model (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_5D96A5CB82D40A1F (workspace_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_group_home_tab (
                hometab_id INT NOT NULL, 
                model_id INT NOT NULL, 
                PRIMARY KEY(hometab_id, model_id), 
                INDEX IDX_E8BB4D96CCE862F (hometab_id), 
                INDEX IDX_E8BB4D967975B7E7 (model_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_resource_model (
                id INT AUTO_INCREMENT NOT NULL, 
                model_id INT NOT NULL, 
                isCopy BOOLEAN NOT NULL, 
                resourceNode_id INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_FC03303AB87FAB32 (resourceNode_id), 
                INDEX IDX_FC03303A7975B7E7 (model_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_user_model 
            ADD CONSTRAINT FK_57DE02DBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_model 
            ADD CONSTRAINT FK_57DE02DB7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_model 
            ADD CONSTRAINT FK_C6568DFFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_model 
            ADD CONSTRAINT FK_C6568DF7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_model 
            ADD CONSTRAINT FK_5D96A5CB82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_home_tab 
            ADD CONSTRAINT FK_E8BB4D96CCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_home_tab 
            ADD CONSTRAINT FK_E8BB4D967975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_model (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_model 
            ADD CONSTRAINT FK_FC03303AB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_model 
            ADD CONSTRAINT FK_FC03303A7975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_model (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_model 
            DROP FOREIGN KEY FK_57DE02DB7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_group_model 
            DROP FOREIGN KEY FK_C6568DF7975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_group_home_tab 
            DROP FOREIGN KEY FK_E8BB4D967975B7E7
        ");
        $this->addSql("
            ALTER TABLE claro_resource_model 
            DROP FOREIGN KEY FK_FC03303A7975B7E7
        ");
        $this->addSql("
            DROP TABLE claro_user_model
        ");
        $this->addSql("
            DROP TABLE claro_group_model
        ");
        $this->addSql("
            DROP TABLE claro_model
        ");
        $this->addSql("
            DROP TABLE claro_group_home_tab
        ");
        $this->addSql("
            DROP TABLE claro_resource_model
        ");
    }
}