<?php

namespace Claroline\SurveyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/09/06 02:25:12
 */
class Version20160906142510 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_survey_simple_text_question_answer (
                id INT AUTO_INCREMENT NOT NULL,
                question_answer_id INT NOT NULL,
                answer_content VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_62C24A08A3E60C9C (question_answer_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_survey_simple_text_question_answer
            ADD CONSTRAINT FK_62C24A08A3E60C9C FOREIGN KEY (question_answer_id)
            REFERENCES claro_survey_question_answer (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_survey_question
            ADD rich_text TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_survey_question_model
            ADD rich_text TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_survey_simple_text_question_answer
        ');
        $this->addSql('
            ALTER TABLE claro_survey_question
            DROP rich_text
        ');
        $this->addSql('
            ALTER TABLE claro_survey_question_model
            DROP rich_text
        ');
    }
}
