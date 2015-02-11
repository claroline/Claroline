<?php

namespace Innova\CollecticielBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/27 02:30:37
 */
class Version20130927143036 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F40A76ED395 ON innova_collecticielbundle_correction (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F404D224760 ON innova_collecticielbundle_correction (drop_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F40A8C6E7BD ON innova_collecticielbundle_correction (drop_zone_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_criterion (
                id INTEGER NOT NULL, 
                drop_zone_id INTEGER NOT NULL, 
                instruction CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F94B3BA7A8C6E7BD ON innova_collecticielbundle_criterion (drop_zone_id)
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
            CREATE INDEX IDX_744084241BAD783F ON innova_collecticielbundle_document (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_744084244D224760 ON innova_collecticielbundle_document (drop_id)
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
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3AD19BA6A8C6E7BD ON innova_collecticielbundle_drop (drop_zone_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3AD19BA6A76ED395 ON innova_collecticielbundle_drop (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3AD19BA65342CDF ON innova_collecticielbundle_drop (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_for_user_in_drop_zone ON innova_collecticielbundle_drop (drop_zone_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_number_in_drop_zone ON innova_collecticielbundle_drop (drop_zone_id, number)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_dropzone (
                id INTEGER NOT NULL, 
                hidden_directory_id INTEGER DEFAULT NULL, 
                edition_state INTEGER NOT NULL, 
                instruction CLOB DEFAULT NULL, 
                allow_workspace_resource BOOLEAN NOT NULL, 
                allow_upload BOOLEAN NOT NULL, 
                allow_url BOOLEAN NOT NULL, 
                allow_rich_text BOOLEAN NOT NULL, 
                peer_review BOOLEAN NOT NULL, 
                expected_total_correction INTEGER NOT NULL, 
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
                total_criteria_column INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC235342CDF ON innova_collecticielbundle_dropzone (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC23B87FAB32 ON innova_collecticielbundle_dropzone (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_grade (
                id INTEGER NOT NULL, 
                criterion_id INTEGER NOT NULL, 
                correction_id INTEGER NOT NULL, 
                value INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B3C52D9397766307 ON innova_collecticielbundle_grade (criterion_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B3C52D9394AE086B ON innova_collecticielbundle_grade (correction_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_grade_for_criterion_and_correction ON innova_collecticielbundle_grade (criterion_id, correction_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_collecticielbundle_correction
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_criterion
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_document
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_drop
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_dropzone
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_grade
        ");
    }
}