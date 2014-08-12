<?php

namespace Claroline\SurveyBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/12 04:23:13
 */
class Version20140812162311 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                published BOOLEAN NOT NULL, 
                closed BOOLEAN NOT NULL, 
                has_public_result BOOLEAN NOT NULL, 
                allow_answer_edition BOOLEAN NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_11B27D4BB87FAB32 (resourceNode_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_survey_choice (
                id INT AUTO_INCREMENT NOT NULL, 
                choice_question_id INT NOT NULL, 
                content TEXT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_C49D43FEA46B3B4F (choice_question_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_relation (
                id INT AUTO_INCREMENT NOT NULL, 
                survey_id INT NOT NULL, 
                question_id INT NOT NULL, 
                question_order INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_953FEEA4B3FE509D (survey_id), 
                INDEX IDX_953FEEA41E27F6BF (question_id), 
                UNIQUE INDEX survey_unique_survey_question_relation (survey_id, question_id), 
                UNIQUE INDEX survey_unique_question_order (survey_id, question_order)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                title TEXT NOT NULL, 
                question TEXT NOT NULL, 
                question_type VARCHAR(255) NOT NULL, 
                comment_allowed BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_1BD4C01382D40A1F (workspace_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                allow_multiple_response BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_388E4C251E27F6BF (question_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_survey_resource 
            ADD CONSTRAINT FK_11B27D4BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
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
            ALTER TABLE claro_survey_question_relation 
            DROP FOREIGN KEY FK_953FEEA4B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            DROP FOREIGN KEY FK_953FEEA41E27F6BF
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