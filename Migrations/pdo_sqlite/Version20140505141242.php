<?php

namespace Claroline\ScormBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/05 02:12:43
 */
class Version20140505141242 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_12_tracking (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                scorm_id INTEGER NOT NULL, 
                score_raw INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                lesson_status VARCHAR(255) DEFAULT NULL, 
                session_time INTEGER DEFAULT NULL, 
                total_time INTEGER DEFAULT NULL, 
                entry VARCHAR(255) DEFAULT NULL, 
                suspend_data CLOB DEFAULT NULL, 
                credit VARCHAR(255) DEFAULT NULL, 
                exit_mode VARCHAR(255) DEFAULT NULL, 
                lesson_location VARCHAR(255) DEFAULT NULL, 
                lesson_mode VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CF939976A76ED395 ON claro_scorm_12_tracking (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CF939976D75F22BE ON claro_scorm_12_tracking (scorm_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_12 (
                id INTEGER NOT NULL, 
                hash_name VARCHAR(50) NOT NULL, 
                mastery_score INTEGER DEFAULT NULL, 
                launch_data CLOB DEFAULT NULL, 
                entry_url VARCHAR(255) NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6FE774D5B87FAB32 ON claro_scorm_12 (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_scorm_12_tracking
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12
        ");
    }
}