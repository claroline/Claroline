<?php

namespace Claroline\ScormBundle\Migrations\pdo_pgsql;

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
                id SERIAL NOT NULL, 
                scorm_resource_id INT NOT NULL, 
                sco_parent_id INT DEFAULT NULL, 
                entry_url VARCHAR(255) DEFAULT NULL, 
                scorm_identifier VARCHAR(255) NOT NULL, 
                title VARCHAR(200) NOT NULL, 
                visible BOOLEAN NOT NULL, 
                parameters VARCHAR(1000) DEFAULT NULL, 
                prerequisites VARCHAR(200) DEFAULT NULL, 
                max_time_allowed VARCHAR(255) DEFAULT NULL, 
                time_limit_action VARCHAR(255) DEFAULT NULL, 
                launch_data VARCHAR(4096) DEFAULT NULL, 
                mastery_score INT DEFAULT NULL, 
                is_block BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
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
                id SERIAL NOT NULL, 
                hash_name VARCHAR(50) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_DB7E0F7CB87FAB32 ON claro_scorm_12_resource (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_12_sco_tracking (
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                scorm_id INT NOT NULL, 
                score_raw INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                lesson_status VARCHAR(255) DEFAULT NULL, 
                session_time INT DEFAULT NULL, 
                total_time INT DEFAULT NULL, 
                entry VARCHAR(255) DEFAULT NULL, 
                suspend_data VARCHAR(4096) DEFAULT NULL, 
                credit VARCHAR(255) DEFAULT NULL, 
                exit_mode VARCHAR(255) DEFAULT NULL, 
                lesson_location VARCHAR(255) DEFAULT NULL, 
                lesson_mode VARCHAR(255) DEFAULT NULL, 
                best_score_raw INT DEFAULT NULL, 
                best_lesson_status VARCHAR(255) DEFAULT NULL, 
                is_locked BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
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
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco 
            ADD CONSTRAINT FK_F900C28948C689D5 FOREIGN KEY (sco_parent_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_resource 
            ADD CONSTRAINT FK_DB7E0F7CB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
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