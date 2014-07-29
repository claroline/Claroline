<?php

namespace Claroline\SurveyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/28 12:22:16
 */
class Version20140728122215 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_question_type (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_survey 
            ADD question_type_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_survey 
            ADD CONSTRAINT FK_5E6CE963CB90598E FOREIGN KEY (question_type_id) 
            REFERENCES claro_survey_question_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_5E6CE963CB90598E ON claro_survey (question_type_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_survey 
            DROP FOREIGN KEY FK_5E6CE963CB90598E
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_type
        ");
        $this->addSql("
            DROP INDEX IDX_5E6CE963CB90598E ON claro_survey
        ");
        $this->addSql("
            ALTER TABLE claro_survey 
            DROP question_type_id
        ");
    }
}