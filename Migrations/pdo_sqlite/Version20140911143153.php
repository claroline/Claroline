<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/11 02:31:54
 */
class Version20140911143153 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_model_user (
                user_id INTEGER NOT NULL, 
                workspacemodel_id INTEGER NOT NULL, 
                PRIMARY KEY(user_id, workspacemodel_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_5318388FA76ED395 ON claro_workspace_model_user (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5318388FD500BD91 ON claro_workspace_model_user (workspacemodel_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_group (
                group_id INTEGER NOT NULL, 
                workspacemodel_id INTEGER NOT NULL, 
                PRIMARY KEY(group_id, workspacemodel_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1F19A8AEFE54D947 ON claro_workspace_model_group (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1F19A8AED500BD91 ON claro_workspace_model_group (workspacemodel_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_536FFC4C82D40A1F ON claro_workspace_model (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_home_tab (
                workspacemodel_id INTEGER NOT NULL, 
                hometab_id INTEGER NOT NULL, 
                PRIMARY KEY(workspacemodel_id, hometab_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A8E0CB1BD500BD91 ON claro_workspace_model_home_tab (workspacemodel_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A8E0CB1BCCE862F ON claro_workspace_model_home_tab (hometab_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model_resource (
                id INTEGER NOT NULL, 
                resource_node_id INTEGER NOT NULL, 
                model_id INTEGER NOT NULL, 
                isCopy BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F5D706351BAD783F ON claro_workspace_model_resource (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F5D706357975B7E7 ON claro_workspace_model_resource (model_id)
        ");
    }

    public function down(Schema $schema)
    {
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