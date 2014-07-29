<?php

namespace Claroline\SurveyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/28 12:22:16
 */
class Version20140728122215 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_question_type (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DROP INDEX UNIQ_5E6CE963B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey AS 
            SELECT id, 
            resourceNode_id 
            FROM claro_survey
        ");
        $this->addSql("
            DROP TABLE claro_survey
        ");
        $this->addSql("
            CREATE TABLE claro_survey (
                id INTEGER NOT NULL, 
                question_type_id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5E6CE963B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_5E6CE963CB90598E FOREIGN KEY (question_type_id) 
                REFERENCES claro_survey_question_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_survey (id, resourceNode_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_survey
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E6CE963B87FAB32 ON claro_survey (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5E6CE963CB90598E ON claro_survey (question_type_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_survey_question_type
        ");
        $this->addSql("
            DROP INDEX IDX_5E6CE963CB90598E
        ");
        $this->addSql("
            DROP INDEX UNIQ_5E6CE963B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey AS 
            SELECT id, 
            resourceNode_id 
            FROM claro_survey
        ");
        $this->addSql("
            DROP TABLE claro_survey
        ");
        $this->addSql("
            CREATE TABLE claro_survey (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5E6CE963B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_survey (id, resourceNode_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_survey
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E6CE963B87FAB32 ON claro_survey (resourceNode_id)
        ");
    }
}