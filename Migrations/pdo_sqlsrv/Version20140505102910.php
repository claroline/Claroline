<?php

namespace Claroline\ScormBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/05 10:29:14
 */
class Version20140505102910 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_12 (
                id INT IDENTITY NOT NULL, 
                hash_name NVARCHAR(50) NOT NULL, 
                mastery_score INT, 
                launch_data VARCHAR(MAX), 
                entry_url NVARCHAR(255) NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6FE774D5B87FAB32 ON claro_scorm_12 (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_12_info (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                scorm_id INT NOT NULL, 
                score_raw INT, 
                score_min INT, 
                score_max INT, 
                lesson_status NVARCHAR(255), 
                session_time INT, 
                total_time INT, 
                entry NVARCHAR(255), 
                suspend_data VARCHAR(MAX), 
                credit NVARCHAR(255), 
                exit_mode NVARCHAR(255), 
                lesson_location NVARCHAR(255), 
                lesson_mode NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7D2C37E3A76ED395 ON claro_scorm_12_info (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7D2C37E3D75F22BE ON claro_scorm_12_info (scorm_id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12 
            ADD CONSTRAINT FK_6FE774D5B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_info 
            ADD CONSTRAINT FK_7D2C37E3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_info 
            ADD CONSTRAINT FK_7D2C37E3D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_12 (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_12_info 
            DROP CONSTRAINT FK_7D2C37E3D75F22BE
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12_info
        ");
    }
}