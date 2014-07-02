<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

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
            sp_RENAME 'claro_activity_past_evaluation.last_date', 
            'evaluation_date', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation ALTER COLUMN evaluation_date DATETIME2(6)
        ");
        $this->addSql("
            sp_RENAME 'claro_activity_evaluation.last_date', 
            'lastest_evaluation_date', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation ALTER COLUMN lastest_evaluation_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD result_visible BIT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_activity_evaluation.lastest_evaluation_date', 
            'last_date', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation ALTER COLUMN last_date DATETIME2(6)
        ");
        $this->addSql("
            sp_RENAME 'claro_activity_past_evaluation.evaluation_date', 
            'last_date', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation ALTER COLUMN last_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP COLUMN result_visible
        ");
    }
}