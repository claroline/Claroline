<?php

namespace Claroline\ScormBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/16 05:09:12
 */
class Version20140716170910 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_2004_sco (
                id INT IDENTITY NOT NULL, 
                scorm_resource_id INT NOT NULL, 
                sco_parent_id INT, 
                entry_url NVARCHAR(255), 
                scorm_identifier NVARCHAR(255) NOT NULL, 
                title NVARCHAR(200) NOT NULL, 
                visible BIT NOT NULL, 
                parameters NVARCHAR(1000), 
                time_limit_action NVARCHAR(255), 
                launch_data NVARCHAR(4000), 
                is_block BIT NOT NULL, 
                max_time_allowed NVARCHAR(255), 
                completion_threshold NUMERIC(10, 7), 
                scaled_passing_score NUMERIC(10, 7), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_E88F1DDD167AFF3D ON claro_scorm_2004_sco (scorm_resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E88F1DDD48C689D5 ON claro_scorm_2004_sco (sco_parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_2004_sco_tracking (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                sco_id INT NOT NULL, 
                score_raw INT, 
                score_min INT, 
                score_max INT, 
                score_scaled NUMERIC(10, 7), 
                completion_status NVARCHAR(255), 
                success_status NVARCHAR(255), 
                total_time NVARCHAR(255), 
                details VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3A61CA76ED395 ON claro_scorm_2004_sco_tracking (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3A61C18A32826 ON claro_scorm_2004_sco_tracking (sco_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_2004_resource (
                id INT IDENTITY NOT NULL, 
                hash_name NVARCHAR(50) NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D16AB015B87FAB32 ON claro_scorm_2004_resource (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            ADD CONSTRAINT FK_E88F1DDD167AFF3D FOREIGN KEY (scorm_resource_id) 
            REFERENCES claro_scorm_2004_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            ADD CONSTRAINT FK_E88F1DDD48C689D5 FOREIGN KEY (sco_parent_id) 
            REFERENCES claro_scorm_2004_sco (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco_tracking 
            ADD CONSTRAINT FK_3A61CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco_tracking 
            ADD CONSTRAINT FK_3A61C18A32826 FOREIGN KEY (sco_id) 
            REFERENCES claro_scorm_2004_sco (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_resource 
            ADD CONSTRAINT FK_D16AB015B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            sp_RENAME 'claro_scorm_12_sco_tracking.scorm_id', 
            'sco_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking ALTER COLUMN sco_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP CONSTRAINT FK_465499F3D75F22BE
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_465499F3D75F22BE'
            ) 
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP CONSTRAINT IDX_465499F3D75F22BE ELSE 
            DROP INDEX IDX_465499F3D75F22BE ON claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F318A32826 FOREIGN KEY (sco_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F318A32826 ON claro_scorm_12_sco_tracking (sco_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            DROP CONSTRAINT FK_E88F1DDD48C689D5
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco_tracking 
            DROP CONSTRAINT FK_3A61C18A32826
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            DROP CONSTRAINT FK_E88F1DDD167AFF3D
        ");
        $this->addSql("
            DROP TABLE claro_scorm_2004_sco
        ");
        $this->addSql("
            DROP TABLE claro_scorm_2004_sco_tracking
        ");
        $this->addSql("
            DROP TABLE claro_scorm_2004_resource
        ");
        $this->addSql("
            sp_RENAME 'claro_scorm_12_sco_tracking.sco_id', 
            'scorm_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking ALTER COLUMN scorm_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP CONSTRAINT FK_465499F318A32826
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_465499F318A32826'
            ) 
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP CONSTRAINT IDX_465499F318A32826 ELSE 
            DROP INDEX IDX_465499F318A32826 ON claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F3D75F22BE ON claro_scorm_12_sco_tracking (scorm_id)
        ");
    }
}