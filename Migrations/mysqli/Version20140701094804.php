<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/01 09:48:06
 */
class Version20140701094804 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation CHANGE last_date evaluation_date DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation CHANGE last_date lastest_evaluation_date DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD result_visible TINYINT(1) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_evaluation CHANGE lastest_evaluation_date last_date DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation CHANGE evaluation_date last_date DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP result_visible
        ");
    }
}