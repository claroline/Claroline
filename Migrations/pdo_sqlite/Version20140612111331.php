<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/12 11:13:33
 */
class Version20140612111331 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_713242A7896F55DB
        ");
        $this->addSql("
            DROP INDEX IDX_713242A71BAD783F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_secondary_resources AS 
            SELECT 
            FROM claro_activity_secondary_resources
        ");
        $this->addSql("
            DROP TABLE claro_activity_secondary_resources
        ");
        $this->addSql("
            CREATE TABLE claro_activity_secondary_resources (
                activityparameters_id INTEGER NOT NULL, 
                resourcenode_id INTEGER NOT NULL, 
                PRIMARY KEY(
                    activityparameters_id, resourcenode_id
                ), 
                CONSTRAINT FK_713242A7DB5E3CF7 FOREIGN KEY (activityparameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_713242A777C292AE FOREIGN KEY (resourcenode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_secondary_resources () 
            SELECT 
            FROM __temp__claro_activity_secondary_resources
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_secondary_resources
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A7DB5E3CF7 ON claro_activity_secondary_resources (activityparameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A777C292AE ON claro_activity_secondary_resources (resourcenode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity AS 
            SELECT id, 
            parameters_id, 
            description, 
            resourceNode_id, 
            title 
            FROM claro_activity
        ");
        $this->addSql("
            DROP TABLE claro_activity
        ");
        $this->addSql("
            CREATE TABLE claro_activity (
                id INTEGER NOT NULL, 
                parameters_id INTEGER DEFAULT NULL, 
                description VARCHAR(255) NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                primaryResource_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_E4A67CAC88BD9C1F FOREIGN KEY (parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_E4A67CAC52410EEC FOREIGN KEY (primaryResource_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (
                id, parameters_id, description, resourceNode_id, 
                title
            ) 
            SELECT id, 
            parameters_id, 
            description, 
            resourceNode_id, 
            title 
            FROM __temp__claro_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC88BD9C1F ON claro_activity (parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CAC52410EEC ON claro_activity (primaryResource_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_E4A67CAC52410EEC
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity AS 
            SELECT id, 
            parameters_id, 
            title, 
            description, 
            resourceNode_id 
            FROM claro_activity
        ");
        $this->addSql("
            DROP TABLE claro_activity
        ");
        $this->addSql("
            CREATE TABLE claro_activity (
                id INTEGER NOT NULL, 
                parameters_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                description VARCHAR(255) NOT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CAC88BD9C1F FOREIGN KEY (parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (
                id, parameters_id, title, description, 
                resourceNode_id
            ) 
            SELECT id, 
            parameters_id, 
            title, 
            description, 
            resourceNode_id 
            FROM __temp__claro_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC88BD9C1F ON claro_activity (parameters_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX IDX_713242A7DB5E3CF7
        ");
        $this->addSql("
            DROP INDEX IDX_713242A777C292AE
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_secondary_resources AS 
            SELECT 
            FROM claro_activity_secondary_resources
        ");
        $this->addSql("
            DROP TABLE claro_activity_secondary_resources
        ");
        $this->addSql("
            CREATE TABLE claro_activity_secondary_resources (
                activity_parameters_id INTEGER NOT NULL, 
                resource_node_id INTEGER NOT NULL, 
                PRIMARY KEY(
                    activity_parameters_id, resource_node_id
                ), 
                CONSTRAINT FK_713242A71BAD783F FOREIGN KEY (resource_node_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_713242A7896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_secondary_resources () 
            SELECT 
            FROM __temp__claro_activity_secondary_resources
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_secondary_resources
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A7896F55DB ON claro_activity_secondary_resources (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A71BAD783F ON claro_activity_secondary_resources (resource_node_id)
        ");
    }
}