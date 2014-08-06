<?php

namespace Claroline\SurveyBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/06 04:18:46
 */
class Version20140806161844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
            ALTER TABLE claro_survey_question ALTER COLUMN title VARCHAR(MAX) NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'unique_question_title_in_workspace'
            ) 
            ALTER TABLE claro_survey_question 
            DROP CONSTRAINT unique_question_title_in_workspace ELSE 
            DROP INDEX unique_question_title_in_workspace ON claro_survey_question
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_survey_choice 
            DROP CONSTRAINT FK_C49D43FEA46B3B4F
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question ALTER COLUMN title NVARCHAR(250) NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_question_title_in_workspace ON claro_survey_question (title, workspace_id) 
            WHERE title IS NOT NULL 
            AND workspace_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_survey_questions_relation 
            DROP CONSTRAINT FK_C764C91B1E27F6BF
        ");
    }
}