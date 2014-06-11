<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/11 02:27:32
 */
class Version20140611142731 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD active_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD active_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD log_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation (log_id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD log_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation ALTER user_id 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation (log_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_activity_unique_evaluation ON claro_activity_evaluation (user_id, activity_parameters_id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD active_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD active_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP CONSTRAINT FK_F75EC869EA675D86
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869EA675D86
        ");
        $this->addSql("
            DROP INDEX user_activity_unique_evaluation
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP log_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation ALTER user_id 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP CONSTRAINT FK_F1A76182EA675D86
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182EA675D86
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP log_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP active_from
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP active_until
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP active_from
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP active_until
        ");
    }
}