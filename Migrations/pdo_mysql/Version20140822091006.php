<?php

namespace Claroline\SurveyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/08/22 09:10:08
 */
class Version20140822091006 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
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
            CREATE TABLE claro_survey_open_ended_question_answer (
                id INT AUTO_INCREMENT NOT NULL, 
                question_answer_id INT NOT NULL, 
                answer_content LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_F2616BBEA3E60C9C (question_answer_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question_answer (
                id INT AUTO_INCREMENT NOT NULL, 
                question_answer_id INT NOT NULL, 
                choice_id INT NOT NULL, 
                INDEX IDX_FDB8AF37A3E60C9C (question_answer_id), 
                INDEX IDX_FDB8AF37998666D1 (choice_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_answer (
                id INT AUTO_INCREMENT NOT NULL, 
                answer_survey_id INT NOT NULL, 
                question_id INT NOT NULL, 
                answer_comment LONGTEXT DEFAULT NULL, 
                INDEX IDX_9F5D3C468E018F4B (answer_survey_id), 
                INDEX IDX_9F5D3C461E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_answer (
                id INT AUTO_INCREMENT NOT NULL, 
                survey_id INT NOT NULL, 
                user_id INT NOT NULL, 
                answer_date DATETIME NOT NULL, 
                nb_answers INT NOT NULL, 
                INDEX IDX_DFEB5349B3FE509D (survey_id), 
                INDEX IDX_DFEB5349A76ED395 (user_id), 
                PRIMARY KEY(id)
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
            CREATE TABLE claro_survey_question_relation (
                id INT AUTO_INCREMENT NOT NULL, 
                survey_id INT NOT NULL, 
                question_id INT NOT NULL, 
                question_order INT NOT NULL, 
                mandatory TINYINT(1) NOT NULL, 
                INDEX IDX_953FEEA4B3FE509D (survey_id), 
                INDEX IDX_953FEEA41E27F6BF (question_id), 
                UNIQUE INDEX survey_unique_survey_question_relation (survey_id, question_id), 
                UNIQUE INDEX survey_unique_question_order (survey_id, question_order), 
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
                comment_label VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_1BD4C01382D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                horizontal TINYINT(1) NOT NULL, 
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
            DROP FOREIGN KEY FK_DFEB5349B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_relation 
            DROP FOREIGN KEY FK_953FEEA4B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_question_answer 
            DROP FOREIGN KEY FK_F2616BBEA3E60C9C
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            DROP FOREIGN KEY FK_FDB8AF37A3E60C9C
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            DROP FOREIGN KEY FK_9F5D3C468E018F4B
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question_answer 
            DROP FOREIGN KEY FK_FDB8AF37998666D1
        ");
        $this->addSql("
            ALTER TABLE claro_survey_question_answer 
            DROP FOREIGN KEY FK_9F5D3C461E27F6BF
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