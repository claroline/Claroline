<?php

namespace Claroline\ScormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/07/16 05:09:11
 */
class Version20140716170910 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_scorm_2004_sco (
                id INT AUTO_INCREMENT NOT NULL, 
                scorm_resource_id INT NOT NULL, 
                sco_parent_id INT DEFAULT NULL, 
                entry_url VARCHAR(255) DEFAULT NULL, 
                scorm_identifier VARCHAR(255) NOT NULL, 
                title VARCHAR(200) NOT NULL, 
                visible TINYINT(1) NOT NULL, 
                parameters VARCHAR(1000) DEFAULT NULL, 
                time_limit_action VARCHAR(255) DEFAULT NULL, 
                launch_data VARCHAR(4000) DEFAULT NULL, 
                is_block TINYINT(1) NOT NULL, 
                max_time_allowed VARCHAR(255) DEFAULT NULL, 
                completion_threshold NUMERIC(10, 7) DEFAULT NULL, 
                scaled_passing_score NUMERIC(10, 7) DEFAULT NULL, 
                INDEX IDX_E88F1DDD167AFF3D (scorm_resource_id), 
                INDEX IDX_E88F1DDD48C689D5 (sco_parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_scorm_2004_sco_tracking (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                sco_id INT NOT NULL, 
                score_raw INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                score_scaled NUMERIC(10, 7) DEFAULT NULL, 
                completion_status VARCHAR(255) DEFAULT NULL, 
                success_status VARCHAR(255) DEFAULT NULL, 
                total_time VARCHAR(255) DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                INDEX IDX_3A61CA76ED395 (user_id), 
                INDEX IDX_3A61C18A32826 (sco_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_scorm_2004_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                hash_name VARCHAR(50) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D16AB015B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco 
            ADD CONSTRAINT FK_E88F1DDD167AFF3D FOREIGN KEY (scorm_resource_id) 
            REFERENCES claro_scorm_2004_resource (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco 
            ADD CONSTRAINT FK_E88F1DDD48C689D5 FOREIGN KEY (sco_parent_id) 
            REFERENCES claro_scorm_2004_sco (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco_tracking 
            ADD CONSTRAINT FK_3A61CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco_tracking 
            ADD CONSTRAINT FK_3A61C18A32826 FOREIGN KEY (sco_id) 
            REFERENCES claro_scorm_2004_sco (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_resource 
            ADD CONSTRAINT FK_D16AB015B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP FOREIGN KEY FK_465499F3D75F22BE
        ');
        $this->addSql('
            DROP INDEX IDX_465499F3D75F22BE ON claro_scorm_12_sco_tracking
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking CHANGE scorm_id sco_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F318A32826 FOREIGN KEY (sco_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_465499F318A32826 ON claro_scorm_12_sco_tracking (sco_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco 
            DROP FOREIGN KEY FK_E88F1DDD48C689D5
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco_tracking 
            DROP FOREIGN KEY FK_3A61C18A32826
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco 
            DROP FOREIGN KEY FK_E88F1DDD167AFF3D
        ');
        $this->addSql('
            DROP TABLE claro_scorm_2004_sco
        ');
        $this->addSql('
            DROP TABLE claro_scorm_2004_sco_tracking
        ');
        $this->addSql('
            DROP TABLE claro_scorm_2004_resource
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP FOREIGN KEY FK_465499F318A32826
        ');
        $this->addSql('
            DROP INDEX IDX_465499F318A32826 ON claro_scorm_12_sco_tracking
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking CHANGE sco_id scorm_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_465499F3D75F22BE ON claro_scorm_12_sco_tracking (scorm_id)
        ');
    }
}
