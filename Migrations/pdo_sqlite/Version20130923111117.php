<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 11:11:17
 */
class Version20130923111117 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_stepType (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_step2resourceNode (
                id INTEGER NOT NULL, 
                step_id INTEGER DEFAULT NULL, 
                resourceOrder INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11F73B21E9C ON innova_step2resourceNode (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11FB87FAB32 ON innova_step2resourceNode (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE innova_user2path (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                path_id INTEGER NOT NULL, 
                status INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5A76ED395 ON innova_user2path (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5D96C566B ON innova_user2path (path_id)
        ");
        $this->addSql("
            CREATE TABLE innova_pathtemplate (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB NOT NULL, 
                step CLOB NOT NULL, 
                user VARCHAR(255) NOT NULL, 
                edit_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_stepWhere (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_stepWho (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_nonDigitalResource (
                id INTEGER NOT NULL, 
                description CLOB NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_305E9E56B87FAB32 ON innova_nonDigitalResource (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id INTEGER NOT NULL, 
                path_id INTEGER DEFAULT NULL, 
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
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567D96C566B ON innova_step (path_id)
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_stepType
        ");
        $this->addSql("
            DROP TABLE innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE innova_user2path
        ");
        $this->addSql("
            DROP TABLE innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE innova_stepWhere
        ");
        $this->addSql("
            DROP TABLE innova_stepWho
        ");
        $this->addSql("
            DROP TABLE innova_nonDigitalResource
        ");
        $this->addSql("
            DROP TABLE innova_step
        ");
    }
}