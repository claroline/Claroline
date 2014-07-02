<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

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
            ALTER TABLE claro_activity_past_evaluation RENAME COLUMN last_date TO evaluation_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation RENAME COLUMN last_date TO lastest_evaluation_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD (
                result_visible NUMBER(1) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_evaluation RENAME COLUMN lastest_evaluation_date TO last_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation RENAME COLUMN evaluation_date TO last_date
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP (result_visible)
        ");
    }
}