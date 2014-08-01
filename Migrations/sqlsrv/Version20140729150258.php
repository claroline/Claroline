<?php

namespace Claroline\SurveyBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/29 03:03:00
 */
class Version20140729150258 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_open_ended_answer (
                id INT IDENTITY NOT NULL, 
                respondent_id INT, 
                survey_id INT NOT NULL, 
                answer_date DATETIME2(6) NOT NULL, 
                content VARCHAR(MAX) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B9DDE645CE80CD19 ON claro_survey_open_ended_answer (respondent_id) 
            WHERE respondent_id IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_B9DDE645B3FE509D ON claro_survey_open_ended_answer (survey_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_open_ended_question (
                id INT IDENTITY NOT NULL, 
                survey_id INT, 
                body VARCHAR(MAX) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C6AAE2AB3FE509D ON claro_survey_open_ended_question (survey_id) 
            WHERE survey_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_survey (
                id INT IDENTITY NOT NULL, 
                question_type NVARCHAR(255) NOT NULL, 
                isPublished BIT NOT NULL, 
                isClosed BIT NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E6CE963B87FAB32 ON claro_survey (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_answer (
                id INT IDENTITY NOT NULL, 
                respondent_id INT, 
                survey_id INT NOT NULL, 
                choice_id INT NOT NULL, 
                answer_date DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E7E7635ECE80CD19 ON claro_survey_multiple_choice_answer (respondent_id) 
            WHERE respondent_id IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_E7E7635EB3FE509D ON claro_survey_multiple_choice_answer (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E7E7635E998666D1 ON claro_survey_multiple_choice_answer (choice_id)
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id INT IDENTITY NOT NULL, 
                survey_id INT, 
                body VARCHAR(MAX) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_388E4C25B3FE509D ON claro_survey_multiple_choice_question (survey_id) 
            WHERE survey_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_choice (
                id INT IDENTITY NOT NULL, 
                question_id INT NOT NULL, 
                content VARCHAR(MAX) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FC9173E91E27F6BF ON claro_survey_multiple_choice_choice (question_id)
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_answer 
            ADD CONSTRAINT FK_B9DDE645CE80CD19 FOREIGN KEY (respondent_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_answer 
            ADD CONSTRAINT FK_B9DDE645B3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_question 
            ADD CONSTRAINT FK_C6AAE2AB3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey 
            ADD CONSTRAINT FK_5E6CE963B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            ADD CONSTRAINT FK_E7E7635ECE80CD19 FOREIGN KEY (respondent_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            ADD CONSTRAINT FK_E7E7635EB3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            ADD CONSTRAINT FK_E7E7635E998666D1 FOREIGN KEY (choice_id) 
            REFERENCES claro_survey_multiple_choice_choice (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            ADD CONSTRAINT FK_388E4C25B3FE509D FOREIGN KEY (survey_id) 
            REFERENCES claro_survey (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_choice 
            ADD CONSTRAINT FK_FC9173E91E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_multiple_choice_question (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_answer 
            DROP CONSTRAINT FK_B9DDE645B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_question 
            DROP CONSTRAINT FK_C6AAE2AB3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            DROP CONSTRAINT FK_E7E7635EB3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            DROP CONSTRAINT FK_388E4C25B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_choice 
            DROP CONSTRAINT FK_FC9173E91E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            DROP CONSTRAINT FK_E7E7635E998666D1
        ");
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