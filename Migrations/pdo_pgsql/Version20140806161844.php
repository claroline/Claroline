<?php

namespace Claroline\SurveyBundle\Migrations\pdo_pgsql;

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
            ALTER TABLE claro_survey_questions_relation 
            ADD CONSTRAINT FK_C764C91B1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES claro_survey_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_survey_choice 
            ADD CONSTRAINT FK_C49D43FEA46B3B4F FOREIGN KEY (choice_question_id) 
            REFERENCES claro_survey_multiple_choice_question (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            DROP INDEX unique_question_title_in_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question ALTER title TYPE TEXT
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question ALTER title TYPE TEXT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_survey_choice 
            DROP CONSTRAINT FK_C49D43FEA46B3B4F
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question ALTER title TYPE VARCHAR(250)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_question_title_in_workspace ON claro_survey_question (title, workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_survey_questions_relation 
            DROP CONSTRAINT FK_C764C91B1E27F6BF
        ");
    }
}