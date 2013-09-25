<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 11:57:40
 */
class Version20130923115740 extends AbstractMigration
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
            CREATE TABLE innova_path (
                id INTEGER NOT NULL,
                path CLOB NOT NULL,
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_CE19F054B87FAB32 ON innova_path (resourceNode_id)
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
            DROP TABLE innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE innova_stepWhere
        ");
        $this->addSql("
            DROP TABLE innova_path
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
