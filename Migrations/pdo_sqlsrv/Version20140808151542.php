<?php

namespace Claroline\SurveyBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/08 03:15:44
 */
class Version20140808151542 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_resource (
                id INT IDENTITY NOT NULL, 
                published BIT NOT NULL, 
                closed BIT NOT NULL, 
                has_public_result BIT NOT NULL, 
                allow_answer_edition BIT NOT NULL, 
                start_date DATETIME2(6), 
                end_date DATETIME2(6), 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_11B27D4BB87FAB32 ON claro_survey_resource (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_survey_questions_relation (
                survey_id INT NOT NULL, 
                question_id INT NOT NULL, 
                PRIMARY KEY (survey_id, question_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C764C91BB3FE509D ON claro_survey_questions_relation (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C764C91B1E27F6BF ON claro_survey_questions_relation (question_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_choice (
                id INT IDENTITY NOT NULL, 
                choice_question_id INT NOT NULL, 
                content VARCHAR(MAX) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C49D43FEA46B3B4F ON claro_survey_choice (choice_question_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question (
                id INT IDENTITY NOT NULL, 
                workspace_id INT NOT NULL, 
                title VARCHAR(MAX) NOT NULL, 
                question VARCHAR(MAX) NOT NULL, 
                question_type NVARCHAR(255) NOT NULL, 
                comment_allowed BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1BD4C01382D40A1F ON claro_survey_question (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id INT IDENTITY NOT NULL, 
                question_id INT, 
                allow_multiple_response BIT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_388E4C251E27F6BF ON claro_survey_multiple_choice_question (question_id) 
            WHERE question_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_survey_resource 
            ADD CONSTRAINT FK_11B27D4BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_questions_relation 
            ADD CONSTRAINT FK_C764C91BB3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey_resource (id)
        ");
        $this->addSql("
            ALTER TABLE claro_survey_questions_relation 
            ADD CONSTRAINT FK_C764C91B1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_question (id)
        ");
        $this->addSql("
            ALTER TABLE claro_survey_choice 
            ADD CONSTRAINT FK_C49D43FEA46B3B4F FOREIGN KEY (choice_question_id) 
            REFERENCES claro_survey_multiple_choice_question (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question 
            ADD CONSTRAINT FK_1BD4C01382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            ADD CONSTRAINT FK_388E4C251E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_question (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_survey_questions_relation 
            DROP CONSTRAINT FK_C764C91BB3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_questions_relation 
            DROP CONSTRAINT FK_C764C91B1E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            DROP CONSTRAINT FK_388E4C251E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_choice 
            DROP CONSTRAINT FK_C49D43FEA46B3B4F
        ");
        $this->addSql("
            DROP TABLE claro_survey_resource
        ");
        $this->addSql("
            DROP TABLE claro_survey_questions_relation
        ");
        $this->addSql("
            DROP TABLE claro_survey_choice
        ");
        $this->addSql("
            DROP TABLE claro_survey_question
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_question
        ");
    }
}