<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170123160000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            UPDATE ujm_response AS a 
            LEFT JOIN ujm_question AS q ON (a.question_id = q.id)
            SET a.question_id = q.uuid
            WHERE q.id IS NOT NULL
        ');

        $this->addSql('
            ALTER TABLE ujm_interaction_hole ADD originalText TEXT DEFAULT NULL;
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            UPDATE ujm_response AS a 
            LEFT JOIN ujm_question AS q ON (a.question_id = q.uuid)
            SET a.question_id = q.id
            WHERE q.id IS NOT NULL
        ');
    }
}
