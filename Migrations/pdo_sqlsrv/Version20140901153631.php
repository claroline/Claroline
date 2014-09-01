<?php

namespace Claroline\SurveyBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/01 03:36:34
 */
class Version20140901153631 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'survey_unique_question_order'
            ) 
            ALTER TABLE claro_survey_question_relation 
            DROP CONSTRAINT survey_unique_question_order ELSE 
            DROP INDEX survey_unique_question_order ON claro_survey_question_relation
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX survey_unique_question_order ON claro_survey_question_relation (survey_id, question_order) 
            WHERE survey_id IS NOT NULL 
            AND question_order IS NOT NULL
        ");
    }
}