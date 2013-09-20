<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/20 09:55:25
 */
class Version20130920095525 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_step2resource (
                id INTEGER NOT NULL, 
                step_id INTEGER DEFAULT NULL, 
                resourceOrder INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_447C595973B21E9C ON innova_step2resource (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_447C5959B87FAB32 ON innova_step2resource (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE innova_resource (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            ADD COLUMN resourceOrder INTEGER NOT NULL
        ");
        $this->addSql("
            DROP INDEX UNIQ_CE19F054B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_path AS 
            SELECT id, 
            resourceNode_id, 
            path 
            FROM innova_path
        ");
        $this->addSql("
            DROP TABLE innova_path
        ");
        $this->addSql("
            CREATE TABLE innova_path (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                path CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_path (id, resourceNode_id, path) 
            SELECT id, 
            resourceNode_id, 
            path 
            FROM __temp__innova_path
        ");
        $this->addSql("
            DROP TABLE __temp__innova_path
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_CE19F054B87FAB32 ON innova_path (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_86F48567B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567DEDC9FF6
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856765544574
        ");
        $this->addSql("
            DROP INDEX IDX_86F485678FE76F3
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step AS 
            SELECT id, 
            instructions, 
            resourceNode_id, 
            parent, 
            expanded, 
            withTutor, 
            withComputer, 
            duration, 
            stepType_id, 
            stepWho_id, 
            stepWhere_id, 
            stepOrder 
            FROM innova_step
        ");
        $this->addSql("
            DROP TABLE innova_step
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id INTEGER NOT NULL, 
                path_id INTEGER DEFAULT NULL, 
                instructions CLOB NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                parent VARCHAR(255) DEFAULT NULL, 
                expanded BOOLEAN NOT NULL, 
                withTutor BOOLEAN NOT NULL, 
                withComputer BOOLEAN NOT NULL, 
                duration DATETIME NOT NULL, 
                stepType_id INTEGER DEFAULT NULL, 
                stepWho_id INTEGER DEFAULT NULL, 
                stepWhere_id INTEGER DEFAULT NULL, 
                stepOrder INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_86F4856765544574 FOREIGN KEY (stepWho_id) 
                REFERENCES innova_stepWho (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F485678FE76F3 FOREIGN KEY (stepWhere_id) 
                REFERENCES innova_stepWhere (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F48567B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
                REFERENCES innova_stepType (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
                REFERENCES innova_path (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step (
                id, instructions, resourceNode_id, 
                parent, expanded, withTutor, withComputer, 
                duration, stepType_id, stepWho_id, 
                stepWhere_id, stepOrder
            ) 
            SELECT id, 
            instructions, 
            resourceNode_id, 
            parent, 
            expanded, 
            withTutor, 
            withComputer, 
            duration, 
            stepType_id, 
            stepWho_id, 
            stepWhere_id, 
            stepOrder 
            FROM __temp__innova_step
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_86F48567B87FAB32 ON innova_step (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567DEDC9FF6 ON innova_step (stepType_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856765544574 ON innova_step (stepWho_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F485678FE76F3 ON innova_step (stepWhere_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567D96C566B ON innova_step (path_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_step2resource
        ");
        $this->addSql("
            DROP TABLE innova_resource
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            ADD COLUMN uuid VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567D96C566B
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567DEDC9FF6
        ");
        $this->addSql("
            DROP INDEX IDX_86F4856765544574
        ");
        $this->addSql("
            DROP INDEX IDX_86F485678FE76F3
        ");
        $this->addSql("
            DROP INDEX UNIQ_86F48567B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step AS 
            SELECT id, 
            stepOrder, 
            parent, 
            expanded, 
            instructions, 
            withTutor, 
            withComputer, 
            duration, 
            stepType_id, 
            stepWho_id, 
            stepWhere_id, 
            resourceNode_id 
            FROM innova_step
        ");
        $this->addSql("
            DROP TABLE innova_step
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id INTEGER NOT NULL, 
                stepOrder INTEGER NOT NULL, 
                parent VARCHAR(255) DEFAULT NULL, 
                expanded BOOLEAN NOT NULL, 
                instructions CLOB NOT NULL, 
                withTutor BOOLEAN NOT NULL, 
                withComputer BOOLEAN NOT NULL, 
                duration DATETIME NOT NULL, 
                stepType_id INTEGER DEFAULT NULL, 
                stepWho_id INTEGER DEFAULT NULL, 
                stepWhere_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                uuid VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
                REFERENCES innova_stepType (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F4856765544574 FOREIGN KEY (stepWho_id) 
                REFERENCES innova_stepWho (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F485678FE76F3 FOREIGN KEY (stepWhere_id) 
                REFERENCES innova_stepWhere (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F48567B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step (
                id, stepOrder, parent, expanded, instructions, 
                withTutor, withComputer, duration, 
                stepType_id, stepWho_id, stepWhere_id, 
                resourceNode_id
            ) 
            SELECT id, 
            stepOrder, 
            parent, 
            expanded, 
            instructions, 
            withTutor, 
            withComputer, 
            duration, 
            stepType_id, 
            stepWho_id, 
            stepWhere_id, 
            resourceNode_id 
            FROM __temp__innova_step
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567DEDC9FF6 ON innova_step (stepType_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856765544574 ON innova_step (stepWho_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F485678FE76F3 ON innova_step (stepWhere_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_86F48567B87FAB32 ON innova_step (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX IDX_21EA11F73B21E9C
        ");
        $this->addSql("
            DROP INDEX IDX_21EA11FB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step2resourceNode AS 
            SELECT id, 
            step_id, 
            resourceNode_id 
            FROM innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE innova_step2resourceNode
        ");
        $this->addSql("
            CREATE TABLE innova_step2resourceNode (
                id INTEGER NOT NULL, 
                step_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_21EA11F73B21E9C FOREIGN KEY (step_id) 
                REFERENCES innova_step (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_21EA11FB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step2resourceNode (id, step_id, resourceNode_id) 
            SELECT id, 
            step_id, 
            resourceNode_id 
            FROM __temp__innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step2resourceNode
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11F73B21E9C ON innova_step2resourceNode (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11FB87FAB32 ON innova_step2resourceNode (resourceNode_id)
        ");
    }
}