<?php

namespace Claroline\SurveyBundle\Migrations\pdo_pgsql;

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
            DROP INDEX survey_unique_question_order
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX survey_unique_question_order ON claro_survey_question_relation (survey_id, question_order)
        ");
    }
}