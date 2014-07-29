<?php

namespace Claroline\SurveyBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/28 12:22:17
 */
class Version20140728122215 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_survey_question_type (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
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
            DROP CONSTRAINT FK_5E6CE963CB90598E
        ");
        $this->addSql("
            DROP TABLE claro_survey_question_type
        ");
        $this->addSql("
            ALTER TABLE claro_survey 
            DROP COLUMN question_type_id
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5E6CE963CB90598E'
            ) 
            ALTER TABLE claro_survey 
            DROP CONSTRAINT IDX_5E6CE963CB90598E ELSE 
            DROP INDEX IDX_5E6CE963CB90598E ON claro_survey
        ");
    }
}