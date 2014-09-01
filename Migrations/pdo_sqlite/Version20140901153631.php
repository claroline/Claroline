<?php

namespace Claroline\SurveyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/01 03:36:33
 */
class Version20140901153631 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX survey_unique_survey_question_relation
        ");
        $this->addSql("
            DROP INDEX survey_unique_question_order
        ");
        $this->addSql("
            DROP INDEX IDX_953FEEA4B3FE509D
        ");
        $this->addSql("
            DROP INDEX IDX_953FEEA41E27F6BF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey_question_relation AS 
            SELECT id, 
            question_id, 
            survey_id, 
            question_order, 
            mandatory 
            FROM claro_survey_question_relation
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_relation
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_relation (
                id INTEGER NOT NULL, 
                question_id INTEGER NOT NULL, 
                survey_id INTEGER NOT NULL, 
                question_order INTEGER NOT NULL, 
                mandatory BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_953FEEA41E27F6BF FOREIGN KEY (question_id) 
                REFERENCES claro_survey_question (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_953FEEA4B3FE509D FOREIGN KEY (survey_id) 
                REFERENCES claro_survey_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_survey_question_relation (
                id, question_id, survey_id, question_order, 
                mandatory
            ) 
            SELECT id, 
            question_id, 
            survey_id, 
            question_order, 
            mandatory 
            FROM __temp__claro_survey_question_relation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey_question_relation
        ");
        $this->addSql("
            CREATE UNIQUE INDEX survey_unique_survey_question_relation ON claro_survey_question_relation (survey_id, question_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_953FEEA4B3FE509D ON claro_survey_question_relation (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_953FEEA41E27F6BF ON claro_survey_question_relation (question_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_953FEEA4B3FE509D
        ");
        $this->addSql("
            DROP INDEX IDX_953FEEA41E27F6BF
        ");
        $this->addSql("
            DROP INDEX survey_unique_survey_question_relation
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_survey_question_relation AS 
            SELECT id, 
            survey_id, 
            question_id, 
            question_order, 
            mandatory 
            FROM claro_survey_question_relation
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_relation
        ");
        $this->addSql("
            CREATE TABLE claro_survey_question_relation (
                id INTEGER NOT NULL, 
                survey_id INTEGER NOT NULL, 
                question_id INTEGER NOT NULL, 
                question_order INTEGER NOT NULL, 
                mandatory BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_953FEEA4B3FE509D FOREIGN KEY (survey_id) 
                REFERENCES claro_survey_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_953FEEA41E27F6BF FOREIGN KEY (question_id) 
                REFERENCES claro_survey_question (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_survey_question_relation (
                id, survey_id, question_id, question_order, 
                mandatory
            ) 
            SELECT id, 
            survey_id, 
            question_id, 
            question_order, 
            mandatory 
            FROM __temp__claro_survey_question_relation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_survey_question_relation
        ");
        $this->addSql("
            CREATE INDEX IDX_953FEEA4B3FE509D ON claro_survey_question_relation (survey_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_953FEEA41E27F6BF ON claro_survey_question_relation (question_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX survey_unique_survey_question_relation ON claro_survey_question_relation (survey_id, question_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX survey_unique_question_order ON claro_survey_question_relation (survey_id, question_order)
        ");
    }
}