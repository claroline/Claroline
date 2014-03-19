<?php

namespace Icap\DropzoneBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/19 01:02:32
 */
class Version20140319130229 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD COLUMN correctionDenied BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD COLUMN correctionDeniedComment CLOB DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD COLUMN diplay_corrections_to_learners BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD COLUMN allow_correction_deny BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_CDA81F40A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_CDA81F404D224760
        ");
        $this->addSql("
            DROP INDEX IDX_CDA81F40A8C6E7BD
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__dropzonebundle_correction AS 
            SELECT id, 
            user_id, 
            drop_id, 
            drop_zone_id, 
            total_grade, 
            comment, 
            valid, 
            start_date, 
            last_open_date, 
            end_date, 
            finished, 
            editable, 
            reporter, 
            reportComment 
            FROM icap__dropzonebundle_correction
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_correction
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_correction (
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_CDA81F40A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_CDA81F404D224760 FOREIGN KEY (drop_id) 
                REFERENCES icap__dropzonebundle_drop (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_CDA81F40A8C6E7BD FOREIGN KEY (drop_zone_id) 
                REFERENCES icap__dropzonebundle_dropzone (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__dropzonebundle_correction (
                id, user_id, drop_id, drop_zone_id, 
                total_grade, comment, valid, start_date, 
                last_open_date, end_date, finished, 
                editable, reporter, reportComment
            ) 
            SELECT id, 
            user_id, 
            drop_id, 
            drop_zone_id, 
            total_grade, 
            comment, 
            valid, 
            start_date, 
            last_open_date, 
            end_date, 
            finished, 
            editable, 
            reporter, 
            reportComment 
            FROM __temp__icap__dropzonebundle_correction
        ");
        $this->addSql("
            DROP TABLE __temp__icap__dropzonebundle_correction
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F40A76ED395 ON icap__dropzonebundle_correction (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F404D224760 ON icap__dropzonebundle_correction (drop_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDA81F40A8C6E7BD ON icap__dropzonebundle_correction (drop_zone_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_6782FC235342CDF
        ");
        $this->addSql("
            DROP INDEX UNIQ_6782FC23B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__dropzonebundle_dropzone AS 
            SELECT id, 
            hidden_directory_id, 
            edition_state, 
            instruction, 
            allow_workspace_resource, 
            allow_upload, 
            allow_url, 
            allow_rich_text, 
            peer_review, 
            expected_total_correction, 
            display_notation_to_learners, 
            display_notation_message_to_learners, 
            minimum_score_to_pass, 
            manual_planning, 
            manual_state, 
            start_allow_drop, 
            end_allow_drop, 
            start_review, 
            end_review, 
            allow_comment_in_correction, 
            total_criteria_column, 
            resourceNode_id 
            FROM icap__dropzonebundle_dropzone
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_dropzone
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_dropzone (
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_6782FC235342CDF FOREIGN KEY (hidden_directory_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6782FC23B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__dropzonebundle_dropzone (
                id, hidden_directory_id, edition_state, 
                instruction, allow_workspace_resource, 
                allow_upload, allow_url, allow_rich_text, 
                peer_review, expected_total_correction, 
                display_notation_to_learners, display_notation_message_to_learners, 
                minimum_score_to_pass, manual_planning, 
                manual_state, start_allow_drop, 
                end_allow_drop, start_review, end_review, 
                allow_comment_in_correction, total_criteria_column, 
                resourceNode_id
            ) 
            SELECT id, 
            hidden_directory_id, 
            edition_state, 
            instruction, 
            allow_workspace_resource, 
            allow_upload, 
            allow_url, 
            allow_rich_text, 
            peer_review, 
            expected_total_correction, 
            display_notation_to_learners, 
            display_notation_message_to_learners, 
            minimum_score_to_pass, 
            manual_planning, 
            manual_state, 
            start_allow_drop, 
            end_allow_drop, 
            start_review, 
            end_review, 
            allow_comment_in_correction, 
            total_criteria_column, 
            resourceNode_id 
            FROM __temp__icap__dropzonebundle_dropzone
        ");
        $this->addSql("
            DROP TABLE __temp__icap__dropzonebundle_dropzone
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC235342CDF ON icap__dropzonebundle_dropzone (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC23B87FAB32 ON icap__dropzonebundle_dropzone (resourceNode_id)
        ");
    }
}