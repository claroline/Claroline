<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/11 02:27:33
 */
class Version20140611142731 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD active_from DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD active_until DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD log_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation (log_id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD log_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation ALTER COLUMN user_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation (log_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_activity_unique_evaluation ON claro_activity_evaluation (user_id, activity_parameters_id) 
            WHERE user_id IS NOT NULL 
            AND activity_parameters_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD active_from DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD active_until DATETIME2(6)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP COLUMN log_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation ALTER COLUMN user_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP CONSTRAINT FK_F75EC869EA675D86
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_F75EC869EA675D86'
            ) 
            ALTER TABLE claro_activity_evaluation 
            DROP CONSTRAINT IDX_F75EC869EA675D86 ELSE 
            DROP INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'user_activity_unique_evaluation'
            ) 
            ALTER TABLE claro_activity_evaluation 
            DROP CONSTRAINT user_activity_unique_evaluation ELSE 
            DROP INDEX user_activity_unique_evaluation ON claro_activity_evaluation
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP COLUMN log_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP CONSTRAINT FK_F1A76182EA675D86
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_F1A76182EA675D86'
            ) 
            ALTER TABLE claro_activity_past_evaluation 
            DROP CONSTRAINT IDX_F1A76182EA675D86 ELSE 
            DROP INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP COLUMN active_from
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP COLUMN active_until
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN active_from
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN active_until
        ");
    }
}