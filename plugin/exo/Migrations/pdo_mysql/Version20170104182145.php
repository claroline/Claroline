<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/04 06:21:47
 */
class Version20170104182145 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD scoreRule LONGTEXT NOT NULL
        ');

        // Move questions scores
        $this->addSql("
            UPDATE ujm_question 
            SET scoreRule='{\"type\": \"sum\"}'
            WHERE mime_type IN (
                'application/x.words+json', 
                'application/x.cloze+json', 
                'application/x.graphic+json',
                'application/x.match+json',
                'application/x.set+json',
                'application/x.pair+json'
            )
        ");

        $this->addSql("
            UPDATE ujm_question AS q
            LEFT JOIN ujm_interaction_open AS o ON (o.question_id = q.id) 
            SET q.scoreRule=CONCAT('{\"type\": \"manual\", \"max\":', o.scoreMaxLongResp, '}')
            WHERE q.mime_type = 'application/x.open+json'
        ");

        $this->addSql("
            UPDATE ujm_question AS q
            LEFT JOIN ujm_interaction_qcm AS c ON (c.question_id = q.id) 
            SET scoreRule='{\"type\": \"sum\"}'
            WHERE q.mime_type = 'application/x.choice+json'
              AND weight_response = 1
        ");

        $this->addSql("
            UPDATE ujm_question AS q
            LEFT JOIN ujm_interaction_qcm AS c ON (c.question_id = q.id) 
            SET q.scoreRule=CONCAT('{\"type\": \"fixed\", \"success\":', IFNULL(c.score_right_response, '0'), ', \"failure\":', IFNULL(c.score_false_response, '0'), '}')
            WHERE q.mime_type = 'application/x.choice+json'
              AND weight_response = 0
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_question 
            DROP scoreRule
        ');
    }
}
