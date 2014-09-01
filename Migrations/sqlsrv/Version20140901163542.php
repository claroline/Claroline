<?php

namespace Claroline\SurveyBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/01 04:35:44
 */
class Version20140901163542 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_resource (
                id INT IDENTITY NOT NULL, 
                description VARCHAR(MAX), 
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
            CREATE TABLE claro_survey_open_ended_question_answer (
                id INT IDENTITY NOT NULL, 
                question_answer_id INT NOT NULL, 
                answer_content VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F2616BBEA3E60C9C ON claro_survey_open_ended_question_answer (question_answer_id) 
            WHERE question_answer_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question_answer (
                id INT IDENTITY NOT NULL, 
                question_answer_id INT NOT NULL, 
                choice_id INT NOT NULL, 
                content VARCHAR(MAX), 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                answer_survey_id INT NOT NULL, 
                question_id INT NOT NULL, 
                answer_comment VARCHAR(MAX), 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                survey_id INT NOT NULL, 
                user_id INT NOT NULL, 
                answer_date DATETIME2(6) NOT NULL, 
                nb_answers INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_DFEB5349B3FE509D ON claro_survey_answer (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_DFEB5349A76ED395 ON claro_survey_answer (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_choice (
                id INT IDENTITY NOT NULL, 
                choice_question_id INT NOT NULL, 
                content VARCHAR(MAX) NOT NULL, 
                other BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C49D43FEA46B3B4F ON claro_survey_choice (choice_question_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_relation (
                id INT IDENTITY NOT NULL, 
                survey_id INT NOT NULL, 
                question_id INT NOT NULL, 
                question_order INT NOT NULL, 
                mandatory BIT NOT NULL, 
                PRIMARY KEY (id)
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
            WHERE survey_id IS NOT NULL 
            AND question_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_model (
                id INT IDENTITY NOT NULL, 
                workspace_id INT NOT NULL, 
                title VARCHAR(MAX) NOT NULL, 
                question_type NVARCHAR(255) NOT NULL, 
                details VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_88BF64F482D40A1F ON claro_survey_question_model (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question (
                id INT IDENTITY NOT NULL, 
                workspace_id INT NOT NULL, 
                title VARCHAR(MAX) NOT NULL, 
                question VARCHAR(MAX) NOT NULL, 
                question_type NVARCHAR(255) NOT NULL, 
                comment_allowed BIT NOT NULL, 
                comment_label NVARCHAR(255), 
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
                horizontal BIT NOT NULL, 
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
            ALTER TABLE claro_survey_open_ended_question_answer 
            ADD CONSTRAINT FK_F2616BBEA3E60C9C FOREIGN KEY (question_answer_id) 
            REFERENCES claro_survey_question_answer (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            ADD CONSTRAINT FK_FDB8AF37A3E60C9C FOREIGN KEY (question_answer_id) 
            REFERENCES claro_survey_question_answer (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            ADD CONSTRAINT FK_FDB8AF37998666D1 FOREIGN KEY (choice_id) 
            REFERENCES claro_survey_choice (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            ADD CONSTRAINT FK_9F5D3C468E018F4B FOREIGN KEY (answer_survey_id) 
            REFERENCES claro_survey_answer (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            ADD CONSTRAINT FK_9F5D3C461E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_question (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_answer 
            ADD CONSTRAINT FK_DFEB5349B3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_answer 
            ADD CONSTRAINT FK_DFEB5349A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_choice 
            ADD CONSTRAINT FK_C49D43FEA46B3B4F FOREIGN KEY (choice_question_id) 
            REFERENCES claro_survey_multiple_choice_question (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            ADD CONSTRAINT FK_953FEEA4B3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            ADD CONSTRAINT FK_953FEEA41E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_question (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_model 
            ADD CONSTRAINT FK_88BF64F482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
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
            ALTER TABLE claro_survey_answer 
            DROP CONSTRAINT FK_DFEB5349B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            DROP CONSTRAINT FK_953FEEA4B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_question_answer 
            DROP CONSTRAINT FK_F2616BBEA3E60C9C
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            DROP CONSTRAINT FK_FDB8AF37A3E60C9C
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            DROP CONSTRAINT FK_9F5D3C468E018F4B
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            DROP CONSTRAINT FK_FDB8AF37998666D1
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            DROP CONSTRAINT FK_9F5D3C461E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            DROP CONSTRAINT FK_953FEEA41E27F6BF
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
            DROP TABLE claro_survey_question_model
        ");
        $this->addSql("
            DROP TABLE claro_survey_question
        ");
        $this->addSql("
            DROP TABLE claro_survey_multiple_choice_question
        ");
    }
}