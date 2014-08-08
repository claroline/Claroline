<?php

namespace Claroline\SurveyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/08 03:15:43
 */
class Version20140808151542 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                published TINYINT(1) NOT NULL, 
                closed TINYINT(1) NOT NULL, 
                has_public_result TINYINT(1) NOT NULL, 
                allow_answer_edition TINYINT(1) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_11B27D4BB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_questions_relation (
                survey_id INT NOT NULL, 
                question_id INT NOT NULL, 
                INDEX IDX_C764C91BB3FE509D (survey_id), 
                INDEX IDX_C764C91B1E27F6BF (question_id), 
                PRIMARY KEY(survey_id, question_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_choice (
                id INT AUTO_INCREMENT NOT NULL, 
                choice_question_id INT NOT NULL, 
                content LONGTEXT NOT NULL, 
                INDEX IDX_C49D43FEA46B3B4F (choice_question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                title LONGTEXT NOT NULL, 
                question LONGTEXT NOT NULL, 
                question_type VARCHAR(255) NOT NULL, 
                comment_allowed TINYINT(1) NOT NULL, 
                INDEX IDX_1BD4C01382D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                allow_multiple_response TINYINT(1) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_388E4C251E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            DROP FOREIGN KEY FK_C764C91BB3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_questions_relation 
            DROP FOREIGN KEY FK_C764C91B1E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            DROP FOREIGN KEY FK_388E4C251E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_choice 
            DROP FOREIGN KEY FK_C49D43FEA46B3B4F
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