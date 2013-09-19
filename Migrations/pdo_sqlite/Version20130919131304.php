<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/19 01:13:04
 */
class Version20130919131304 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_86F48567B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step AS 
            SELECT id, 
            instructions, 
            resourceNode_id, 
            uuid, 
            \"order\", 
            parent, 
            expanded, 
            withTutor, 
            withComputer, 
            duration, 
            deployable 
            FROM innova_step
        ");
        $this->addSql("
            DROP TABLE innova_step
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id INTEGER NOT NULL, 
                instructions CLOB NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                uuid VARCHAR(255) NOT NULL, 
                \"order\" INTEGER NOT NULL, 
                parent VARCHAR(255) NOT NULL, 
                expanded BOOLEAN NOT NULL, 
                withTutor BOOLEAN NOT NULL, 
                withComputer BOOLEAN NOT NULL, 
                duration DATETIME NOT NULL, 
                deployable BOOLEAN NOT NULL, 
                stepType_id INTEGER DEFAULT NULL, 
                stepWho_id INTEGER DEFAULT NULL, 
                stepWhere_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_86F48567B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
                REFERENCES innova_stepType (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F4856765544574 FOREIGN KEY (stepWho_id) 
                REFERENCES innova_stepWho (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_86F485678FE76F3 FOREIGN KEY (stepWhere_id) 
                REFERENCES innova_stepWhere (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step (
                id, instructions, resourceNode_id, 
                uuid, \"order\", parent, expanded, withTutor, 
                withComputer, duration, deployable
            ) 
            SELECT id, 
            instructions, 
            resourceNode_id, 
            uuid, 
            \"order\", 
            parent, 
            expanded, 
            withTutor, 
            withComputer, 
            duration, 
            deployable 
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
    }

    public function down(Schema $schema)
    {
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
            uuid, 
            \"order\", 
            parent, 
            expanded, 
            instructions, 
            withTutor, 
            withComputer, 
            duration, 
            deployable, 
            resourceNode_id 
            FROM innova_step
        ");
        $this->addSql("
            DROP TABLE innova_step
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id INTEGER NOT NULL, 
                uuid VARCHAR(255) NOT NULL, 
                \"order\" INTEGER NOT NULL, 
                parent VARCHAR(255) NOT NULL, 
                expanded BOOLEAN NOT NULL, 
                instructions CLOB NOT NULL, 
                withTutor BOOLEAN NOT NULL, 
                withComputer BOOLEAN NOT NULL, 
                duration DATETIME NOT NULL, 
                deployable BOOLEAN NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_86F48567B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step (
                id, uuid, \"order\", parent, expanded, 
                instructions, withTutor, withComputer, 
                duration, deployable, resourceNode_id
            ) 
            SELECT id, 
            uuid, 
            \"order\", 
            parent, 
            expanded, 
            instructions, 
            withTutor, 
            withComputer, 
            duration, 
            deployable, 
            resourceNode_id 
            FROM __temp__innova_step
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_86F48567B87FAB32 ON innova_step (resourceNode_id)
        ");
    }
}