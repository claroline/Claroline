<?php

namespace Claroline\SurveyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/29 03:02:59
 */
class Version20140729150258 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_open_ended_answer (
                id INTEGER NOT NULL, 
                respondent_id INTEGER DEFAULT NULL, 
                survey_id INTEGER NOT NULL, 
                answer_date DATETIME NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B9DDE645CE80CD19 ON claro_survey_open_ended_answer (respondent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B9DDE645B3FE509D ON claro_survey_open_ended_answer (survey_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_open_ended_question (
                id INTEGER NOT NULL, 
                survey_id INTEGER DEFAULT NULL, 
                body CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C6AAE2AB3FE509D ON claro_survey_open_ended_question (survey_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey (
                id INTEGER NOT NULL, 
                question_type VARCHAR(255) NOT NULL, 
                isPublished BOOLEAN NOT NULL, 
                isClosed BOOLEAN NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E6CE963B87FAB32 ON claro_survey (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_answer (
                id INTEGER NOT NULL, 
                respondent_id INTEGER DEFAULT NULL, 
                survey_id INTEGER NOT NULL, 
                choice_id INTEGER NOT NULL, 
                answer_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E7E7635ECE80CD19 ON claro_survey_multiple_choice_answer (respondent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E7E7635EB3FE509D ON claro_survey_multiple_choice_answer (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E7E7635E998666D1 ON claro_survey_multiple_choice_answer (choice_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id INTEGER NOT NULL, 
                survey_id INTEGER DEFAULT NULL, 
                body CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_388E4C25B3FE509D ON claro_survey_multiple_choice_question (survey_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_choice (
                id INTEGER NOT NULL, 
                question_id INTEGER NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FC9173E91E27F6BF ON claro_survey_multiple_choice_choice (question_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_survey_open_ended_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_open_ended_question
        ");
        $this->addSql("
            DROP TABLE claro_survey
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_answer
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_question
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_choice
        ");
    }
}