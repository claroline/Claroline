<?php

namespace Claroline\ScormBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 09:07:25
 */
class Version20180531091928 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_scorm_sco (
                id INT AUTO_INCREMENT NOT NULL, 
                scorm_id INT NOT NULL, 
                sco_parent_id INT DEFAULT NULL, 
                entry_url VARCHAR(255) DEFAULT NULL, 
                identifier VARCHAR(255) NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                visible TINYINT(1) NOT NULL, 
                sco_parameters LONGTEXT DEFAULT NULL, 
                launch_data LONGTEXT DEFAULT NULL, 
                max_time_allowed VARCHAR(255) DEFAULT NULL, 
                time_limit_action VARCHAR(255) DEFAULT NULL, 
                block TINYINT(1) NOT NULL, 
                score_int INT DEFAULT NULL, 
                score_decimal NUMERIC(10, 7) DEFAULT NULL, 
                completion_threshold NUMERIC(10, 7) DEFAULT NULL, 
                prerequisites VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_296767D0D17F50A6 (uuid), 
                INDEX IDX_296767D0D75F22BE (scorm_id), 
                INDEX IDX_296767D048C689D5 (sco_parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_scorm (
                id INT AUTO_INCREMENT NOT NULL, 
                version VARCHAR(255) NOT NULL, 
                hash_name VARCHAR(255) NOT NULL, 
                ratio DOUBLE PRECISION DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B6416871D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_B6416871B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_scorm_12_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                hash_name VARCHAR(255) NOT NULL, 
                hide_top_bar TINYINT(1) DEFAULT '0' NOT NULL, 
                exit_mode INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_DB7E0F7CB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_scorm_12_sco_tracking (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                sco_id INT NOT NULL, 
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
                INDEX IDX_465499F318A32826 (sco_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_scorm_2004_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                hash_name VARCHAR(255) NOT NULL, 
                hide_top_bar TINYINT(1) DEFAULT '0' NOT NULL, 
                exit_mode INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D16AB015B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_sco_tracking (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                sco_id INT NOT NULL, 
                score_raw INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                score_scaled NUMERIC(10, 7) DEFAULT NULL, 
                lesson_status VARCHAR(255) DEFAULT NULL, 
                completion_status VARCHAR(255) DEFAULT NULL, 
                session_time INT DEFAULT NULL, 
                total_time_int INT DEFAULT NULL, 
                total_time_string VARCHAR(255) DEFAULT NULL, 
                entry VARCHAR(255) DEFAULT NULL, 
                suspend_data LONGTEXT DEFAULT NULL, 
                credit VARCHAR(255) DEFAULT NULL, 
                exit_mode VARCHAR(255) DEFAULT NULL, 
                lesson_location VARCHAR(255) DEFAULT NULL, 
                lesson_mode VARCHAR(255) DEFAULT NULL, 
                is_locked TINYINT(1) DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                latest_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_2627E972D17F50A6 (uuid), 
                INDEX IDX_2627E972A76ED395 (user_id), 
                INDEX IDX_2627E97218A32826 (sco_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_scorm_sco 
            ADD CONSTRAINT FK_296767D0D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_sco 
            ADD CONSTRAINT FK_296767D048C689D5 FOREIGN KEY (sco_parent_id) 
            REFERENCES claro_scorm_sco (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm 
            ADD CONSTRAINT FK_B6416871B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_resource 
            ADD CONSTRAINT FK_DB7E0F7CB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking 
            ADD CONSTRAINT FK_465499F318A32826 FOREIGN KEY (sco_id) 
            REFERENCES claro_scorm_12_sco (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_resource 
            ADD CONSTRAINT FK_D16AB015B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_scorm_sco_tracking 
            ADD CONSTRAINT FK_2627E972A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_sco_tracking 
            ADD CONSTRAINT FK_2627E97218A32826 FOREIGN KEY (sco_id) 
            REFERENCES claro_scorm_sco (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scorm_sco 
            DROP FOREIGN KEY FK_296767D048C689D5
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_sco_tracking 
            DROP FOREIGN KEY FK_2627E97218A32826
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_sco 
            DROP FOREIGN KEY FK_296767D0D75F22BE
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco 
            DROP FOREIGN KEY FK_F900C289167AFF3D
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco 
            DROP FOREIGN KEY FK_F900C28948C689D5
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_12_sco_tracking 
            DROP FOREIGN KEY FK_465499F318A32826
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco 
            DROP FOREIGN KEY FK_E88F1DDD167AFF3D
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco 
            DROP FOREIGN KEY FK_E88F1DDD48C689D5
        ');
        $this->addSql('
            ALTER TABLE claro_scorm_2004_sco_tracking 
            DROP FOREIGN KEY FK_3A61C18A32826
        ');
        $this->addSql('
            DROP TABLE claro_scorm_sco
        ');
        $this->addSql('
            DROP TABLE claro_scorm
        ');
        $this->addSql('
            DROP TABLE claro_scorm_12_resource
        ');
        $this->addSql('
            DROP TABLE claro_scorm_12_sco
        ');
        $this->addSql('
            DROP TABLE claro_scorm_12_sco_tracking
        ');
        $this->addSql('
            DROP TABLE claro_scorm_2004_resource
        ');
        $this->addSql('
            DROP TABLE claro_scorm_2004_sco
        ');
        $this->addSql('
            DROP TABLE claro_scorm_2004_sco_tracking
        ');
        $this->addSql('
            DROP TABLE claro_scorm_sco_tracking
        ');
    }
}
