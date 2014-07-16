<?php

namespace Claroline\ScormBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/16 03:35:33
 */
class Version20140716153531 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_2004_sco (
                id SERIAL NOT NULL, 
                scorm_resource_id INT NOT NULL, 
                sco_parent_id INT DEFAULT NULL, 
                entry_url VARCHAR(255) DEFAULT NULL, 
                scorm_identifier VARCHAR(255) NOT NULL, 
                title VARCHAR(200) NOT NULL, 
                visible BOOLEAN NOT NULL, 
                parameters VARCHAR(1000) DEFAULT NULL, 
                time_limit_action VARCHAR(255) DEFAULT NULL, 
                launch_data VARCHAR(4000) DEFAULT NULL, 
                is_block BOOLEAN NOT NULL, 
                max_time_allowed VARCHAR(255) DEFAULT NULL, 
                completion_threshold NUMERIC(10, 7) DEFAULT NULL, 
                scaled_passing_score NUMERIC(10, 7) DEFAULT NULL, 
                PRIMARY KEY(id)
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
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                scorm_id INT NOT NULL, 
                score_raw INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                score_scaled NUMERIC(10, 7) DEFAULT NULL, 
                completion_status VARCHAR(255) DEFAULT NULL, 
                success_status VARCHAR(255) DEFAULT NULL, 
                total_time VARCHAR(255) DEFAULT NULL, 
                details TEXT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3A61CA76ED395 ON claro_scorm_2004_sco_tracking (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3A61CD75F22BE ON claro_scorm_2004_sco_tracking (scorm_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_scorm_2004_sco_tracking.details IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_2004_resource (
                id SERIAL NOT NULL, 
                hash_name VARCHAR(50) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D16AB015B87FAB32 ON claro_scorm_2004_resource (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            ADD CONSTRAINT FK_E88F1DDD167AFF3D FOREIGN KEY (scorm_resource_id) 
            REFERENCES claro_scorm_2004_resource (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco 
            ADD CONSTRAINT FK_E88F1DDD48C689D5 FOREIGN KEY (sco_parent_id) 
            REFERENCES claro_scorm_2004_sco (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco_tracking 
            ADD CONSTRAINT FK_3A61CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_sco_tracking 
            ADD CONSTRAINT FK_3A61CD75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_2004_sco (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_2004_resource 
            ADD CONSTRAINT FK_D16AB015B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
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
            DROP CONSTRAINT FK_3A61CD75F22BE
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
    }
}