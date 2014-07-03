<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/23 05:36:37
 */
class Version20140623173635 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_general_facet_preference (
                id INT IDENTITY NOT NULL, 
                role_id INT NOT NULL, 
                baseData BIT NOT NULL, 
                mail BIT NOT NULL, 
                phone BIT NOT NULL, 
                sendMail BIT NOT NULL, 
                sendMessage BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_38AACF88D60322AC ON claro_general_facet_preference (role_id)
        ");
        $this->addSql("
            ALTER TABLE claro_general_facet_preference 
            ADD CONSTRAINT FK_38AACF88D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_general_facet_preference
        ");
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
    }
}