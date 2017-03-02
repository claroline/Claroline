<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/01/04 08:34:24
 */
class Version20170104203421 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            DROP score_right_response, 
            DROP score_false_response, 
            DROP weight_response
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            DROP scoreMaxLongResp
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_interaction_open 
            ADD scoreMaxLongResp DOUBLE PRECISION DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_qcm 
            ADD score_right_response DOUBLE PRECISION DEFAULT NULL, 
            ADD score_false_response DOUBLE PRECISION DEFAULT NULL, 
            ADD weight_response TINYINT(1) NOT NULL
        ');
    }
}
