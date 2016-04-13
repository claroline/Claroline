<?php

namespace Innova\CollecticielBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/02/12 10:23:19
 */
class Version20150212102316 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_criterion (
                id SERIAL NOT NULL, 
                drop_zone_id INT NOT NULL, 
                instruction TEXT NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_CC96E6A6A8C6E7BD ON innova_collecticielbundle_criterion (drop_zone_id)
        ');
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_drop (
                id SERIAL NOT NULL, 
                drop_zone_id INT NOT NULL, 
                user_id INT NOT NULL, 
                hidden_directory_id INT DEFAULT NULL, 
                drop_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                reported BOOLEAN NOT NULL, 
                finished BOOLEAN NOT NULL, 
                number INT NOT NULL, 
                auto_closed_drop BOOLEAN DEFAULT 'false' NOT NULL, 
                unlocked_drop BOOLEAN DEFAULT 'false' NOT NULL, 
                unlocked_user BOOLEAN DEFAULT 'false' NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql('
            CREATE INDEX IDX_71757239A8C6E7BD ON innova_collecticielbundle_drop (drop_zone_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_71757239A76ED395 ON innova_collecticielbundle_drop (user_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_717572395342CDF ON innova_collecticielbundle_drop (hidden_directory_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX unique_drop_for_user_in_drop_zone ON innova_collecticielbundle_drop (drop_zone_id, user_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX unique_drop_number_in_drop_zone ON innova_collecticielbundle_drop (drop_zone_id, number)
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_grade (
                id SERIAL NOT NULL, 
                criterion_id INT NOT NULL, 
                correction_id INT NOT NULL, 
                value SMALLINT NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_D33E07AF97766307 ON innova_collecticielbundle_grade (criterion_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_D33E07AF94AE086B ON innova_collecticielbundle_grade (correction_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX unique_grade_for_criterion_and_correction ON innova_collecticielbundle_grade (criterion_id, correction_id)
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_document (
                id SERIAL NOT NULL, 
                resource_node_id INT DEFAULT NULL, 
                drop_id INT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_1C357F0C1BAD783F ON innova_collecticielbundle_document (resource_node_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document (drop_id)
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_correction (
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                drop_id INT DEFAULT NULL, 
                drop_zone_id INT NOT NULL, 
                total_grade NUMERIC(10, 2) DEFAULT NULL, 
                comment TEXT DEFAULT NULL, 
                valid BOOLEAN NOT NULL, 
                start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                last_open_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                finished BOOLEAN NOT NULL, 
                editable BOOLEAN NOT NULL, 
                reporter BOOLEAN NOT NULL, 
                reportComment TEXT DEFAULT NULL, 
                correctionDenied BOOLEAN NOT NULL, 
                correctionDeniedComment TEXT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_BA9AF20BA76ED395 ON innova_collecticielbundle_correction (user_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_BA9AF20B4D224760 ON innova_collecticielbundle_correction (drop_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_BA9AF20BA8C6E7BD ON innova_collecticielbundle_correction (drop_zone_id)
        ');
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_dropzone (
                id SERIAL NOT NULL, 
                hidden_directory_id INT DEFAULT NULL, 
                event_agenda_drop INT DEFAULT NULL, 
                event_agenda_correction INT DEFAULT NULL, 
                edition_state SMALLINT NOT NULL, 
                instruction TEXT DEFAULT NULL, 
                correction_instruction TEXT DEFAULT NULL, 
                success_message TEXT DEFAULT NULL, 
                fail_message TEXT DEFAULT NULL, 
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
                start_allow_drop TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                end_allow_drop TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                start_review TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                end_review TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                allow_comment_in_correction BOOLEAN NOT NULL, 
                force_comment_in_correction BOOLEAN NOT NULL, 
                diplay_corrections_to_learners BOOLEAN NOT NULL, 
                allow_correction_deny BOOLEAN NOT NULL, 
                total_criteria_column SMALLINT NOT NULL, 
                auto_close_opened_drops_when_time_is_up BOOLEAN DEFAULT 'false' NOT NULL, 
                auto_close_state VARCHAR(255) DEFAULT 'waiting' NOT NULL, 
                notify_on_drop BOOLEAN DEFAULT 'false' NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_FF7070B5342CDF ON innova_collecticielbundle_dropzone (hidden_directory_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_FF7070BE6B974D2 ON innova_collecticielbundle_dropzone (event_agenda_drop)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_FF7070B8D9E1321 ON innova_collecticielbundle_dropzone (event_agenda_correction)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_FF7070BB87FAB32 ON innova_collecticielbundle_dropzone (resourceNode_id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_criterion 
            ADD CONSTRAINT FK_CC96E6A6A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_drop 
            ADD CONSTRAINT FK_71757239A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_drop 
            ADD CONSTRAINT FK_71757239A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_drop 
            ADD CONSTRAINT FK_717572395342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_grade 
            ADD CONSTRAINT FK_D33E07AF97766307 FOREIGN KEY (criterion_id) 
            REFERENCES innova_collecticielbundle_criterion (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_grade 
            ADD CONSTRAINT FK_D33E07AF94AE086B FOREIGN KEY (correction_id) 
            REFERENCES innova_collecticielbundle_correction (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            ADD CONSTRAINT FK_1C357F0C1BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            ADD CONSTRAINT FK_1C357F0C4D224760 FOREIGN KEY (drop_id) 
            REFERENCES innova_collecticielbundle_drop (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            ADD CONSTRAINT FK_BA9AF20BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            ADD CONSTRAINT FK_BA9AF20B4D224760 FOREIGN KEY (drop_id) 
            REFERENCES innova_collecticielbundle_drop (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            ADD CONSTRAINT FK_BA9AF20BA8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070B5342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070BE6B974D2 FOREIGN KEY (event_agenda_drop) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070B8D9E1321 FOREIGN KEY (event_agenda_correction) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_grade 
            DROP CONSTRAINT FK_D33E07AF97766307
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            DROP CONSTRAINT FK_1C357F0C4D224760
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            DROP CONSTRAINT FK_BA9AF20B4D224760
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_grade 
            DROP CONSTRAINT FK_D33E07AF94AE086B
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_criterion 
            DROP CONSTRAINT FK_CC96E6A6A8C6E7BD
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_drop 
            DROP CONSTRAINT FK_71757239A8C6E7BD
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            DROP CONSTRAINT FK_BA9AF20BA8C6E7BD
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_criterion
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_drop
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_grade
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_document
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_correction
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_dropzone
        ');
    }
}
