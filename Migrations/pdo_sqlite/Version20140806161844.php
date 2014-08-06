<?php

namespace Claroline\SurveyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/06 04:18:45
 */
class Version20140806161844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_C764C91BB3FE509D
        ");
        $this->addSql("
            DROP INDEX IDX_C764C91B1E27F6BF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey_questions_relation AS 
            SELECT survey_id, 
            question_id 
            FROM claro_survey_questions_relation
        ");
        $this->addSql("
            DROP TABLE claro_survey_questions_relation
        ");
        $this->addSql("
            CREATE TABLE claro_survey_questions_relation (
                survey_id INTEGER NOT NULL, 
                question_id INTEGER NOT NULL, 
                PRIMARY KEY(survey_id, question_id), 
                CONSTRAINT FK_C764C91BB3FE509D FOREIGN KEY (survey_id) 
                REFERENCES claro_survey_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_C764C91B1E27F6BF FOREIGN KEY (question_id) 
                REFERENCES claro_survey_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_survey_questions_relation (survey_id, question_id) 
            SELECT survey_id, 
            question_id 
            FROM __temp__claro_survey_questions_relation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey_questions_relation
        ");
        $this->addSql("
            CREATE INDEX IDX_C764C91BB3FE509D ON claro_survey_questions_relation (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C764C91B1E27F6BF ON claro_survey_questions_relation (question_id)
        ");
        $this->addSql("
            DROP INDEX IDX_C49D43FEA46B3B4F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey_choice AS 
            SELECT id, 
            choice_question_id, 
            content 
            FROM claro_survey_choice
        ");
        $this->addSql("
            DROP TABLE claro_survey_choice
        ");
        $this->addSql("
            CREATE TABLE claro_survey_choice (
                id INTEGER NOT NULL, 
                choice_question_id INTEGER NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_C49D43FEA46B3B4F FOREIGN KEY (choice_question_id) 
                REFERENCES claro_survey_multiple_choice_question (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_survey_choice (id, choice_question_id, content) 
            SELECT id, 
            choice_question_id, 
            content 
            FROM __temp__claro_survey_choice
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey_choice
        ");
        $this->addSql("
            CREATE INDEX IDX_C49D43FEA46B3B4F ON claro_survey_choice (choice_question_id)
        ");
        $this->addSql("
            DROP INDEX unique_question_title_in_workspace
        ");
        $this->addSql("
            DROP INDEX IDX_1BD4C013C54C8C93
        ");
        $this->addSql("
            DROP INDEX IDX_1BD4C01382D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey_question AS 
            SELECT id, 
            workspace_id, 
            type_id, 
            title, 
            question 
            FROM claro_survey_question
        ");
        $this->addSql("
            DROP TABLE claro_survey_question
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question (
                id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                type_id INTEGER NOT NULL, 
                question CLOB NOT NULL, 
                title CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1BD4C01382D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1BD4C013C54C8C93 FOREIGN KEY (type_id) 
                REFERENCES claro_survey_question_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_survey_question (
                id, workspace_id, type_id, title, question
            ) 
            SELECT id, 
            workspace_id, 
            type_id, 
            title, 
            question 
            FROM __temp__claro_survey_question
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey_question
        ");
        $this->addSql("
            CREATE INDEX IDX_1BD4C013C54C8C93 ON claro_survey_question (type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1BD4C01382D40A1F ON claro_survey_question (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_C49D43FEA46B3B4F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey_choice AS 
            SELECT id, 
            choice_question_id, 
            content 
            FROM claro_survey_choice
        ");
        $this->addSql("
            DROP TABLE claro_survey_choice
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
            INSERT INTO claro_survey_choice (id, choice_question_id, content) 
            SELECT id, 
            choice_question_id, 
            content 
            FROM __temp__claro_survey_choice
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey_choice
        ");
        $this->addSql("
            CREATE INDEX IDX_C49D43FEA46B3B4F ON claro_survey_choice (choice_question_id)
        ");
        $this->addSql("
            DROP INDEX IDX_1BD4C013C54C8C93
        ");
        $this->addSql("
            DROP INDEX IDX_1BD4C01382D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey_question AS 
            SELECT id, 
            type_id, 
            workspace_id, 
            title, 
            question 
            FROM claro_survey_question
        ");
        $this->addSql("
            DROP TABLE claro_survey_question
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question (
                id INTEGER NOT NULL, 
                type_id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                question CLOB NOT NULL, 
                title VARCHAR(250) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1BD4C013C54C8C93 FOREIGN KEY (type_id) 
                REFERENCES claro_survey_question_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1BD4C01382D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_survey_question (
                id, type_id, workspace_id, title, question
            ) 
            SELECT id, 
            type_id, 
            workspace_id, 
            title, 
            question 
            FROM __temp__claro_survey_question
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey_question
        ");
        $this->addSql("
            CREATE INDEX IDX_1BD4C013C54C8C93 ON claro_survey_question (type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1BD4C01382D40A1F ON claro_survey_question (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_question_title_in_workspace ON claro_survey_question (title, workspace_id)
        ");
        $this->addSql("
            DROP INDEX IDX_C764C91BB3FE509D
        ");
        $this->addSql("
            DROP INDEX IDX_C764C91B1E27F6BF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey_questions_relation AS 
            SELECT survey_id, 
            question_id 
            FROM claro_survey_questions_relation
        ");
        $this->addSql("
            DROP TABLE claro_survey_questions_relation
        ");
        $this->addSql("
            CREATE TABLE claro_survey_questions_relation (
                survey_id INTEGER NOT NULL, 
                question_id INTEGER NOT NULL, 
                PRIMARY KEY(survey_id, question_id), 
                CONSTRAINT FK_C764C91BB3FE509D FOREIGN KEY (survey_id) 
                REFERENCES claro_survey_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_survey_questions_relation (survey_id, question_id) 
            SELECT survey_id, 
            question_id 
            FROM __temp__claro_survey_questions_relation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey_questions_relation
        ");
        $this->addSql("
            CREATE INDEX IDX_C764C91BB3FE509D ON claro_survey_questions_relation (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C764C91B1E27F6BF ON claro_survey_questions_relation (question_id)
        ");
    }
}