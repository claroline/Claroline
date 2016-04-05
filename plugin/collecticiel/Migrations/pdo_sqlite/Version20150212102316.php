<?php

namespace Innova\CollecticielBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/12 10:23:19
 */
class Version20150212102316 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_criterion (
                id INTEGER NOT NULL, 
                drop_zone_id INTEGER NOT NULL, 
                instruction CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CC96E6A6A8C6E7BD ON innova_collecticielbundle_criterion (drop_zone_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_drop (
                id INTEGER NOT NULL, 
                drop_zone_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                hidden_directory_id INTEGER DEFAULT NULL, 
                drop_date DATETIME NOT NULL, 
                reported BOOLEAN NOT NULL, 
                finished BOOLEAN NOT NULL, 
                number INTEGER NOT NULL, 
                auto_closed_drop BOOLEAN DEFAULT '0' NOT NULL, 
                unlocked_drop BOOLEAN DEFAULT '0' NOT NULL, 
                unlocked_user BOOLEAN DEFAULT '0' NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_71757239A8C6E7BD ON innova_collecticielbundle_drop (drop_zone_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_71757239A76ED395 ON innova_collecticielbundle_drop (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_717572395342CDF ON innova_collecticielbundle_drop (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_for_user_in_drop_zone ON innova_collecticielbundle_drop (drop_zone_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_number_in_drop_zone ON innova_collecticielbundle_drop (drop_zone_id, number)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_grade (
                id INTEGER NOT NULL, 
                criterion_id INTEGER NOT NULL, 
                correction_id INTEGER NOT NULL, 
                value SMALLINT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D33E07AF97766307 ON innova_collecticielbundle_grade (criterion_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D33E07AF94AE086B ON innova_collecticielbundle_grade (correction_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_grade_for_criterion_and_correction ON innova_collecticielbundle_grade (criterion_id, correction_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_document (
                id INTEGER NOT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                drop_id INTEGER NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1C357F0C1BAD783F ON innova_collecticielbundle_document (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document (drop_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_correction (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                drop_id INTEGER DEFAULT NULL, 
                drop_zone_id INTEGER NOT NULL, 
                total_grade NUMERIC(10, 2) DEFAULT NULL, 
                comment CLOB DEFAULT NULL, 
                valid BOOLEAN NOT NULL, 
                start_date DATETIME NOT NULL, 
                last_open_date DATETIME NOT NULL, 
                end_date DATETIME DEFAULT NULL, 
                finished BOOLEAN NOT NULL, 
                editable BOOLEAN NOT NULL, 
                reporter BOOLEAN NOT NULL, 
                reportComment CLOB DEFAULT NULL, 
                correctionDenied BOOLEAN NOT NULL, 
                correctionDeniedComment CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_BA9AF20BA76ED395 ON innova_collecticielbundle_correction (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_BA9AF20B4D224760 ON innova_collecticielbundle_correction (drop_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_BA9AF20BA8C6E7BD ON innova_collecticielbundle_correction (drop_zone_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_dropzone (
                id INTEGER NOT NULL, 
                hidden_directory_id INTEGER DEFAULT NULL, 
                event_agenda_drop INTEGER DEFAULT NULL, 
                event_agenda_correction INTEGER DEFAULT NULL, 
                edition_state SMALLINT NOT NULL, 
                instruction CLOB DEFAULT NULL, 
                correction_instruction CLOB DEFAULT NULL, 
                success_message CLOB DEFAULT NULL, 
                fail_message CLOB DEFAULT NULL, 
                allow_workspace_resource BOOLEAN NOT NULL, 
                allow_upload BOOLEAN NOT NULL, 
                allow_url BOOLEAN NOT NULL, 
                allow_rich_text BOOLEAN NOT NULL, 
                peer_review BOOLEAN NOT NULL, 
                expected_total_correction SMALLINT NOT NULL, 
                display_notation_to_learners BOOLEAN NOT NULL, 
                display_notation_message_to_learners BOOLEAN NOT NULL, 
                minimum_score_to_pass DOUBLE PRECISION NOT NULL, 
                manual_planning BOOLEAN NOT NULL, 
                manual_state VARCHAR(255) NOT NULL, 
                start_allow_drop DATETIME DEFAULT NULL, 
                end_allow_drop DATETIME DEFAULT NULL, 
                start_review DATETIME DEFAULT NULL, 
                end_review DATETIME DEFAULT NULL, 
                allow_comment_in_correction BOOLEAN NOT NULL, 
                force_comment_in_correction BOOLEAN NOT NULL, 
                diplay_corrections_to_learners BOOLEAN NOT NULL, 
                allow_correction_deny BOOLEAN NOT NULL, 
                total_criteria_column SMALLINT NOT NULL, 
                auto_close_opened_drops_when_time_is_up BOOLEAN DEFAULT '0' NOT NULL, 
                auto_close_state VARCHAR(255) DEFAULT 'waiting' NOT NULL, 
                notify_on_drop BOOLEAN DEFAULT '0' NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FF7070B5342CDF ON innova_collecticielbundle_dropzone (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FF7070BE6B974D2 ON innova_collecticielbundle_dropzone (event_agenda_drop)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FF7070B8D9E1321 ON innova_collecticielbundle_dropzone (event_agenda_correction)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FF7070BB87FAB32 ON innova_collecticielbundle_dropzone (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_collecticielbundle_criterion
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_drop
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_grade
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_document
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_correction
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_dropzone
        ");
    }
}