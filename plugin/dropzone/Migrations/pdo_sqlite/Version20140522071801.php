<?php

namespace Icap\DropzoneBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/05/22 07:18:06
 */
class Version20140522071801 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD COLUMN unlocked_drop BOOLEAN DEFAULT '0' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD COLUMN unlocked_user BOOLEAN DEFAULT '0' NOT NULL
        ");
        $this->addSql('
            DROP INDEX UNIQ_6782FC235342CDF
        ');
        $this->addSql('
            DROP INDEX UNIQ_6782FC23B87FAB32
        ');
        $this->addSql('
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
            resourceNode_id, 
            diplay_corrections_to_learners, 
            allow_correction_deny, 
            correction_instruction, 
            success_message, 
            fail_message, 
            auto_close_opened_drops_when_time_is_up, 
            auto_close_state 
            FROM icap__dropzonebundle_dropzone
        ');
        $this->addSql('
            DROP TABLE icap__dropzonebundle_dropzone
        ');
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
                diplay_corrections_to_learners BOOLEAN NOT NULL, 
                allow_correction_deny BOOLEAN NOT NULL, 
                correction_instruction CLOB DEFAULT NULL, 
                success_message CLOB DEFAULT NULL, 
                fail_message CLOB DEFAULT NULL, 
                auto_close_opened_drops_when_time_is_up BOOLEAN DEFAULT '0' NOT NULL, 
                auto_close_state VARCHAR(255) DEFAULT 'waiting' NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6782FC235342CDF FOREIGN KEY (hidden_directory_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6782FC23B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql('
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
                resourceNode_id, diplay_corrections_to_learners, 
                allow_correction_deny, correction_instruction, 
                success_message, fail_message, auto_close_opened_drops_when_time_is_up, 
                auto_close_state
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
            resourceNode_id, 
            diplay_corrections_to_learners, 
            allow_correction_deny, 
            correction_instruction, 
            success_message, 
            fail_message, 
            auto_close_opened_drops_when_time_is_up, 
            auto_close_state 
            FROM __temp__icap__dropzonebundle_dropzone
        ');
        $this->addSql('
            DROP TABLE __temp__icap__dropzonebundle_dropzone
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_6782FC235342CDF ON icap__dropzonebundle_dropzone (hidden_directory_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_6782FC23B87FAB32 ON icap__dropzonebundle_dropzone (resourceNode_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX IDX_3AD19BA6A8C6E7BD
        ');
        $this->addSql('
            DROP INDEX IDX_3AD19BA6A76ED395
        ');
        $this->addSql('
            DROP INDEX UNIQ_3AD19BA65342CDF
        ');
        $this->addSql('
            DROP INDEX unique_drop_for_user_in_drop_zone
        ');
        $this->addSql('
            DROP INDEX unique_drop_number_in_drop_zone
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__icap__dropzonebundle_drop AS 
            SELECT id, 
            drop_zone_id, 
            user_id, 
            hidden_directory_id, 
            drop_date, 
            reported, 
            finished, 
            number, 
            auto_closed_drop 
            FROM icap__dropzonebundle_drop
        ');
        $this->addSql('
            DROP TABLE icap__dropzonebundle_drop
        ');
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_drop (
                id INTEGER NOT NULL, 
                drop_zone_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                hidden_directory_id INTEGER DEFAULT NULL, 
                drop_date DATETIME NOT NULL, 
                reported BOOLEAN NOT NULL, 
                finished BOOLEAN NOT NULL, 
                number INTEGER NOT NULL, 
                auto_closed_drop BOOLEAN DEFAULT '0' NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3AD19BA6A8C6E7BD FOREIGN KEY (drop_zone_id) 
                REFERENCES icap__dropzonebundle_dropzone (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_3AD19BA6A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_3AD19BA65342CDF FOREIGN KEY (hidden_directory_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql('
            INSERT INTO icap__dropzonebundle_drop (
                id, drop_zone_id, user_id, hidden_directory_id, 
                drop_date, reported, finished, number, 
                auto_closed_drop
            ) 
            SELECT id, 
            drop_zone_id, 
            user_id, 
            hidden_directory_id, 
            drop_date, 
            reported, 
            finished, 
            number, 
            auto_closed_drop 
            FROM __temp__icap__dropzonebundle_drop
        ');
        $this->addSql('
            DROP TABLE __temp__icap__dropzonebundle_drop
        ');
        $this->addSql('
            CREATE INDEX IDX_3AD19BA6A8C6E7BD ON icap__dropzonebundle_drop (drop_zone_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_3AD19BA6A76ED395 ON icap__dropzonebundle_drop (user_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_3AD19BA65342CDF ON icap__dropzonebundle_drop (hidden_directory_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX unique_drop_for_user_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, user_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX unique_drop_number_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, number)
        ');
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD COLUMN notify_on_drop BOOLEAN DEFAULT '0' NOT NULL
        ");
    }
}
