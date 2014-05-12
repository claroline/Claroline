<?php

namespace Claroline\ScormBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/12 11:23:10
 */
class Version20140512112307 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_12_sco (
                id INT IDENTITY NOT NULL, 
                scorm_resource_id INT NOT NULL, 
                sco_parent_id INT, 
                entry_url NVARCHAR(255), 
                scorm_identifier NVARCHAR(255) NOT NULL, 
                title NVARCHAR(200) NOT NULL, 
                visible BIT NOT NULL, 
                parameters NVARCHAR(1000), 
                prerequisites NVARCHAR(200), 
                max_time_allowed NVARCHAR(255), 
                time_limit_action NVARCHAR(255), 
                launch_data VARCHAR(MAX), 
                mastery_score INT, 
                is_block BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F900C289167AFF3D ON claro_scorm_12_sco (scorm_resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F900C28948C689D5 ON claro_scorm_12_sco (sco_parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_12_resource (
                id INT IDENTITY NOT NULL, 
                hash_name NVARCHAR(50) NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_DB7E0F7CB87FAB32 ON claro_scorm_12_resource (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_12_sco_tracking (
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
                best_score_raw INT, 
                best_lesson_status NVARCHAR(255), 
                is_locked BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F3A76ED395 ON claro_scorm_12_sco_tracking (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F3D75F22BE ON claro_scorm_12_sco_tracking (scorm_id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco 
            ADD CONSTRAINT FK_F900C289167AFF3D FOREIGN KEY (scorm_resource_id) 
            REFERENCES claro_scorm_12_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco 
            ADD CONSTRAINT FK_F900C28948C689D5 FOREIGN KEY (sco_parent_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_resource 
            ADD CONSTRAINT FK_DB7E0F7CB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco 
            DROP CONSTRAINT FK_F900C28948C689D5
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP CONSTRAINT FK_465499F3D75F22BE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco 
            DROP CONSTRAINT FK_F900C289167AFF3D
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12_sco
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12_resource
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12_sco_tracking
        ");
    }
}