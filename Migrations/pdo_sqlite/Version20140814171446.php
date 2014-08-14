<?php

namespace Claroline\SurveyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/14 05:14:47
 */
class Version20140814171446 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_resource (
                id INTEGER NOT NULL, 
                published BOOLEAN NOT NULL, 
                closed BOOLEAN NOT NULL, 
                has_public_result BOOLEAN NOT NULL, 
                allow_answer_edition BOOLEAN NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_11B27D4BB87FAB32 ON claro_survey_resource (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_open_ended_question_answer (
                id INTEGER NOT NULL, 
                question_answer_id INTEGER NOT NULL, 
                answer_content CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F2616BBEA3E60C9C ON claro_survey_open_ended_question_answer (question_answer_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question_answer (
                id INTEGER NOT NULL, 
                question_answer_id INTEGER NOT NULL, 
                choice_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FDB8AF37A3E60C9C ON claro_survey_multiple_choice_question_answer (question_answer_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FDB8AF37998666D1 ON claro_survey_multiple_choice_question_answer (choice_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_answer (
                id INTEGER NOT NULL, 
                answer_survey_id INTEGER NOT NULL, 
                question_id INTEGER NOT NULL, 
                answer_comment CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_9F5D3C468E018F4B ON claro_survey_question_answer (answer_survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_9F5D3C461E27F6BF ON claro_survey_question_answer (question_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_answer (
                id INTEGER NOT NULL, 
                answer_date DATETIME NOT NULL, 
                nb_answers INTEGER NOT NULL, 
                survey_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_DFEB5349B3FE509D ON claro_survey_answer (survey_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_choice (
                id INTEGER NOT NULL, 
                choice_question_id INTEGER NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C49D43FEA46B3B4F ON claro_survey_choice (choice_question_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_relation (
                id INTEGER NOT NULL, 
                survey_id INTEGER NOT NULL, 
                question_id INTEGER NOT NULL, 
                question_order INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_953FEEA4B3FE509D ON claro_survey_question_relation (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_953FEEA41E27F6BF ON claro_survey_question_relation (question_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX survey_unique_survey_question_relation ON claro_survey_question_relation (survey_id, question_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX survey_unique_question_order ON claro_survey_question_relation (survey_id, question_order)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question (
                id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                title CLOB NOT NULL, 
                question CLOB NOT NULL, 
                question_type VARCHAR(255) NOT NULL, 
                comment_allowed BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1BD4C01382D40A1F ON claro_survey_question (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id INTEGER NOT NULL, 
                question_id INTEGER DEFAULT NULL, 
                allow_multiple_response BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_388E4C251E27F6BF ON claro_survey_multiple_choice_question (question_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_survey_resource
        ");
        $this->addSql("
            DROP TABLE claro_survey_open_ended_question_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_question_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_choice
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_relation
        ");
        $this->addSql("
            DROP TABLE claro_survey_question
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_question
        ");
    }
}