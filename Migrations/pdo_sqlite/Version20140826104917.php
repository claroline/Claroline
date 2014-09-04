<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/26 10:49:18
 */
class Version20140826104917 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            DROP INDEX IDX_E4A67CAC52410EEC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity AS 
            SELECT id, 
            parameters_id, 
            description, 
            resourceNode_id, 
            title, 
            primaryResource_id 
            FROM claro_activity
        ");
        $this->addSql("
            DROP TABLE claro_activity
        ");
        $this->addSql("
            CREATE TABLE claro_activity (
                id INTEGER NOT NULL, 
                parameters_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                primaryResource_id INTEGER DEFAULT NULL, 
                description CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CAC52410EEC FOREIGN KEY (primaryResource_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_E4A67CAC88BD9C1F FOREIGN KEY (parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (
                id, parameters_id, description, resourceNode_id, 
                title, primaryResource_id
            ) 
            SELECT id, 
            parameters_id, 
            description, 
            resourceNode_id, 
            title, 
            primaryResource_id 
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
            primaryResource_id, 
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
                primaryResource_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                description VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CAC52410EEC FOREIGN KEY (primaryResource_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_E4A67CAC88BD9C1F FOREIGN KEY (parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (
                id, parameters_id, title, description, 
                primaryResource_id, resourceNode_id
            ) 
            SELECT id, 
            parameters_id, 
            title, 
            description, 
            primaryResource_id, 
            resourceNode_id 
            FROM __temp__claro_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CAC52410EEC ON claro_activity (primaryResource_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC88BD9C1F ON claro_activity (parameters_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
    }
}