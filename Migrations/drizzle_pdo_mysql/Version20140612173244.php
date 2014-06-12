<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/12 05:32:46
 */
class Version20140612173244 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD active_from DATETIME DEFAULT NULL, 
            ADD active_until DATETIME DEFAULT NULL, 
            DROP additional_datas
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD log_id INT DEFAULT NULL
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
            ADD log_id INT DEFAULT NULL, 
            CHANGE user_id user_id INT NOT NULL
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
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD active_from DATETIME DEFAULT NULL, 
            ADD active_until DATETIME DEFAULT NULL, 
            DROP additional_datas
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP FOREIGN KEY FK_F75EC869EA675D86
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation
        ");
        $this->addSql("
            DROP INDEX user_activity_unique_evaluation ON claro_activity_evaluation
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP log_id, 
            CHANGE user_id user_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP FOREIGN KEY FK_F1A76182EA675D86
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP log_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD additional_datas VARCHAR(255) DEFAULT NULL, 
            DROP active_from, 
            DROP active_until
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD additional_datas VARCHAR(255) DEFAULT NULL, 
            DROP active_from, 
            DROP active_until
        ");
    }
}