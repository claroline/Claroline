<?php

namespace Claroline\SurveyBundle\Migrations\mysqli;

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
                id INT AUTO_INCREMENT NOT NULL, 
                respondent_id INT DEFAULT NULL, 
                survey_id INT NOT NULL, 
                answer_date DATETIME NOT NULL, 
                content LONGTEXT NOT NULL, 
                UNIQUE INDEX UNIQ_B9DDE645CE80CD19 (respondent_id), 
                INDEX IDX_B9DDE645B3FE509D (survey_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_open_ended_question (
                id INT AUTO_INCREMENT NOT NULL, 
                survey_id INT DEFAULT NULL, 
                body LONGTEXT NOT NULL, 
                UNIQUE INDEX UNIQ_C6AAE2AB3FE509D (survey_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey (
                id INT AUTO_INCREMENT NOT NULL, 
                question_type VARCHAR(255) NOT NULL, 
                isPublished TINYINT(1) NOT NULL, 
                isClosed TINYINT(1) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5E6CE963B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_answer (
                id INT AUTO_INCREMENT NOT NULL, 
                respondent_id INT DEFAULT NULL, 
                survey_id INT NOT NULL, 
                choice_id INT NOT NULL, 
                answer_date DATETIME NOT NULL, 
                UNIQUE INDEX UNIQ_E7E7635ECE80CD19 (respondent_id), 
                INDEX IDX_E7E7635EB3FE509D (survey_id), 
                INDEX IDX_E7E7635E998666D1 (choice_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_question (
                id INT AUTO_INCREMENT NOT NULL, 
                survey_id INT DEFAULT NULL, 
                body LONGTEXT NOT NULL, 
                UNIQUE INDEX UNIQ_388E4C25B3FE509D (survey_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_survey_multiple_choice_choice (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT NOT NULL, 
                content LONGTEXT NOT NULL, 
                INDEX IDX_FC9173E91E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            DROP FOREIGN KEY FK_B9DDE645B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_open_ended_question 
            DROP FOREIGN KEY FK_C6AAE2AB3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            DROP FOREIGN KEY FK_E7E7635EB3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_question 
            DROP FOREIGN KEY FK_388E4C25B3FE509D
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_choice 
            DROP FOREIGN KEY FK_FC9173E91E27F6BF
        ");
        $this->addSql("
            ALTER TABLE claro_survey_multiple_choice_answer 
            DROP FOREIGN KEY FK_E7E7635E998666D1
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