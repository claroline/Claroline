<?php

namespace Claroline\ScormBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/16 05:09:11
 */
class Version20140716170910 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_2004_sco (
                id INTEGER NOT NULL, 
                scorm_resource_id INTEGER NOT NULL, 
                sco_parent_id INTEGER DEFAULT NULL, 
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
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                sco_id INTEGER NOT NULL, 
                score_raw INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                score_scaled NUMERIC(10, 7) DEFAULT NULL, 
                completion_status VARCHAR(255) DEFAULT NULL, 
                success_status VARCHAR(255) DEFAULT NULL, 
                total_time VARCHAR(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
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
                id INTEGER NOT NULL, 
                hash_name VARCHAR(50) NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D16AB015B87FAB32 ON claro_scorm_2004_resource (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX IDX_465499F3A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_465499F3D75F22BE
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_scorm_12_sco_tracking AS 
            SELECT id, 
            scorm_id, 
            user_id, 
            score_raw, 
            score_min, 
            score_max, 
            lesson_status, 
            session_time, 
            total_time, 
            entry, 
            suspend_data, 
            credit, 
            exit_mode, 
            lesson_location, 
            lesson_mode, 
            best_score_raw, 
            best_lesson_status, 
            is_locked 
            FROM claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_12_sco_tracking (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                sco_id INTEGER NOT NULL, 
                score_raw INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                lesson_status VARCHAR(255) DEFAULT NULL, 
                session_time INTEGER DEFAULT NULL, 
                total_time INTEGER DEFAULT NULL, 
                entry VARCHAR(255) DEFAULT NULL, 
                suspend_data CLOB DEFAULT NULL, 
                credit VARCHAR(255) DEFAULT NULL, 
                exit_mode VARCHAR(255) DEFAULT NULL, 
                lesson_location VARCHAR(255) DEFAULT NULL, 
                lesson_mode VARCHAR(255) DEFAULT NULL, 
                best_score_raw INTEGER DEFAULT NULL, 
                best_lesson_status VARCHAR(255) DEFAULT NULL, 
                is_locked BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_465499F3A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_465499F318A32826 FOREIGN KEY (sco_id) 
                REFERENCES claro_scorm_12_sco (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_scorm_12_sco_tracking (
                id, sco_id, user_id, score_raw, score_min, 
                score_max, lesson_status, session_time, 
                total_time, entry, suspend_data, 
                credit, exit_mode, lesson_location, 
                lesson_mode, best_score_raw, best_lesson_status, 
                is_locked
            ) 
            SELECT id, 
            scorm_id, 
            user_id, 
            score_raw, 
            score_min, 
            score_max, 
            lesson_status, 
            session_time, 
            total_time, 
            entry, 
            suspend_data, 
            credit, 
            exit_mode, 
            lesson_location, 
            lesson_mode, 
            best_score_raw, 
            best_lesson_status, 
            is_locked 
            FROM __temp__claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            DROP TABLE __temp__claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F3A76ED395 ON claro_scorm_12_sco_tracking (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F318A32826 ON claro_scorm_12_sco_tracking (sco_id)
        ");
    }

    public function down(Schema $schema)
    {
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
            DROP INDEX IDX_465499F3A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_465499F318A32826
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_scorm_12_sco_tracking AS 
            SELECT id, 
            user_id, 
            sco_id, 
            score_raw, 
            score_min, 
            score_max, 
            lesson_status, 
            session_time, 
            total_time, 
            entry, 
            suspend_data, 
            credit, 
            exit_mode, 
            lesson_location, 
            lesson_mode, 
            best_score_raw, 
            best_lesson_status, 
            is_locked 
            FROM claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_12_sco_tracking (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                scorm_id INTEGER NOT NULL, 
                score_raw INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                lesson_status VARCHAR(255) DEFAULT NULL, 
                session_time INTEGER DEFAULT NULL, 
                total_time INTEGER DEFAULT NULL, 
                entry VARCHAR(255) DEFAULT NULL, 
                suspend_data CLOB DEFAULT NULL, 
                credit VARCHAR(255) DEFAULT NULL, 
                exit_mode VARCHAR(255) DEFAULT NULL, 
                lesson_location VARCHAR(255) DEFAULT NULL, 
                lesson_mode VARCHAR(255) DEFAULT NULL, 
                best_score_raw INTEGER DEFAULT NULL, 
                best_lesson_status VARCHAR(255) DEFAULT NULL, 
                is_locked BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_465499F3A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_465499F3D75F22BE FOREIGN KEY (scorm_id) 
                REFERENCES claro_scorm_12_sco (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_scorm_12_sco_tracking (
                id, user_id, scorm_id, score_raw, score_min, 
                score_max, lesson_status, session_time, 
                total_time, entry, suspend_data, 
                credit, exit_mode, lesson_location, 
                lesson_mode, best_score_raw, best_lesson_status, 
                is_locked
            ) 
            SELECT id, 
            user_id, 
            sco_id, 
            score_raw, 
            score_min, 
            score_max, 
            lesson_status, 
            session_time, 
            total_time, 
            entry, 
            suspend_data, 
            credit, 
            exit_mode, 
            lesson_location, 
            lesson_mode, 
            best_score_raw, 
            best_lesson_status, 
            is_locked 
            FROM __temp__claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            DROP TABLE __temp__claro_scorm_12_sco_tracking
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F3A76ED395 ON claro_scorm_12_sco_tracking (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_465499F3D75F22BE ON claro_scorm_12_sco_tracking (scorm_id)
        ");
    }
}