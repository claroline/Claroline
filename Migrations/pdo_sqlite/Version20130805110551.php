<?php

namespace Claroline\ScormBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/05 11:05:52
 */
class Version20130805110551 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_info (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                scorm_id INTEGER DEFAULT NULL, 
                score_raw INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                lesson_status VARCHAR(255) DEFAULT NULL, 
                session_time INTEGER DEFAULT NULL, 
                total_time INTEGER DEFAULT NULL, 
                entry VARCHAR(255) DEFAULT NULL, 
                suspend_data VARCHAR(255) DEFAULT NULL, 
                credit VARCHAR(255) DEFAULT NULL, 
                exit_mode VARCHAR(255) DEFAULT NULL, 
                lesson_location VARCHAR(255) DEFAULT NULL, 
                lesson_mode VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6F4BB916A76ED395 ON claro_scorm_info (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6F4BB916D75F22BE ON claro_scorm_info (scorm_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm (
                id INTEGER NOT NULL, 
                hash_name VARCHAR(36) NOT NULL, 
                mastery_score INTEGER DEFAULT NULL, 
                launch_data VARCHAR(255) DEFAULT NULL, 
                entry_url VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_scorm_info
        ");
        $this->addSql("
            DROP TABLE claro_scorm
        ");
    }
}