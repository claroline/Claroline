<?php

namespace Claroline\ScormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/05/12 11:23:09
 */
class Version20140512112307 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_scorm_12_sco (
                id INT AUTO_INCREMENT NOT NULL, 
                scorm_resource_id INT NOT NULL, 
                sco_parent_id INT DEFAULT NULL, 
                entry_url VARCHAR(255) DEFAULT NULL, 
                scorm_identifier VARCHAR(255) NOT NULL, 
                title VARCHAR(200) NOT NULL, 
                visible TINYINT(1) NOT NULL, 
                parameters VARCHAR(1000) DEFAULT NULL, 
                prerequisites VARCHAR(200) DEFAULT NULL, 
                max_time_allowed VARCHAR(255) DEFAULT NULL, 
                time_limit_action VARCHAR(255) DEFAULT NULL, 
                launch_data VARCHAR(4096) DEFAULT NULL, 
                mastery_score INT DEFAULT NULL, 
                is_block TINYINT(1) NOT NULL, 
                INDEX IDX_F900C289167AFF3D (scorm_resource_id), 
                INDEX IDX_F900C28948C689D5 (sco_parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_scorm_12_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                hash_name VARCHAR(50) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_DB7E0F7CB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_scorm_12_sco_tracking (
                id INT AUTO_INCREMENT NOT NULL, 
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
                is_locked TINYINT(1) NOT NULL, 
                INDEX IDX_465499F3A76ED395 (user_id), 
                INDEX IDX_465499F3D75F22BE (scorm_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco 
            ADD CONSTRAINT FK_F900C289167AFF3D FOREIGN KEY (scorm_resource_id) 
            REFERENCES claro_scorm_12_resource (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco 
            ADD CONSTRAINT FK_F900C28948C689D5 FOREIGN KEY (sco_parent_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_resource 
            ADD CONSTRAINT FK_DB7E0F7CB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco 
            DROP FOREIGN KEY FK_F900C28948C689D5
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP FOREIGN KEY FK_465499F3D75F22BE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco 
            DROP FOREIGN KEY FK_F900C289167AFF3D
        ');
        $this->addSql('
            DROP TABLE claro_scorm_12_sco
        ');
        $this->addSql('
            DROP TABLE claro_scorm_12_resource
        ');
        $this->addSql('
            DROP TABLE claro_scorm_12_sco_tracking
        ');
    }
}
