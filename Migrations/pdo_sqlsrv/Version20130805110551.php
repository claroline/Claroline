<?php

namespace Claroline\ScormBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/05 11:05:53
 */
class Version20130805110551 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_info (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                scorm_id INT, 
                score_raw INT, 
                score_min INT, 
                score_max INT, 
                lesson_status NVARCHAR(255), 
                session_time INT, 
                total_time INT, 
                entry NVARCHAR(255), 
                suspend_data NVARCHAR(255), 
                credit NVARCHAR(255), 
                exit_mode NVARCHAR(255), 
                lesson_location NVARCHAR(255), 
                lesson_mode NVARCHAR(255), 
                PRIMARY KEY (id)
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
                id INT NOT NULL, 
                hash_name NVARCHAR(36) NOT NULL, 
                mastery_score INT, 
                launch_data NVARCHAR(255), 
                entry_url NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_info 
            ADD CONSTRAINT FK_6F4BB916A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_info 
            ADD CONSTRAINT FK_6F4BB916D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm (id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm 
            ADD CONSTRAINT FK_B6416871BF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_info 
            DROP CONSTRAINT FK_6F4BB916D75F22BE
        ");
        $this->addSql("
            DROP TABLE claro_scorm_info
        ");
        $this->addSql("
            DROP TABLE claro_scorm
        ");
    }
}