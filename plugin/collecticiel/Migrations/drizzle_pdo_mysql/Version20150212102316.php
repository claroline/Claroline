<?php

namespace Innova\CollecticielBundle\Migrations\drizzle_pdo_mysql;

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
                id INT AUTO_INCREMENT NOT NULL, 
                drop_zone_id INT NOT NULL, 
                instruction TEXT NOT NULL, 
                INDEX IDX_CC96E6A6A8C6E7BD (drop_zone_id), 
                PRIMARY KEY(id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_drop (
                id INT AUTO_INCREMENT NOT NULL, 
                drop_zone_id INT NOT NULL, 
                user_id INT NOT NULL, 
                hidden_directory_id INT DEFAULT NULL, 
                drop_date DATETIME NOT NULL, 
                reported BOOLEAN NOT NULL, 
                finished BOOLEAN NOT NULL, 
                number INT NOT NULL, 
                auto_closed_drop BOOLEAN DEFAULT 'false' NOT NULL, 
                unlocked_drop BOOLEAN DEFAULT 'false' NOT NULL, 
                unlocked_user BOOLEAN DEFAULT 'false' NOT NULL, 
                INDEX IDX_71757239A8C6E7BD (drop_zone_id), 
                INDEX IDX_71757239A76ED395 (user_id), 
                UNIQUE INDEX UNIQ_717572395342CDF (hidden_directory_id), 
                UNIQUE INDEX unique_drop_for_user_in_drop_zone (drop_zone_id, user_id), 
                UNIQUE INDEX unique_drop_number_in_drop_zone (drop_zone_id, number), 
                PRIMARY KEY(id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_grade (
                id INT AUTO_INCREMENT NOT NULL, 
                criterion_id INT NOT NULL, 
                correction_id INT NOT NULL, 
                `value` INT NOT NULL, 
                INDEX IDX_D33E07AF97766307 (criterion_id), 
                INDEX IDX_D33E07AF94AE086B (correction_id), 
                UNIQUE INDEX unique_grade_for_criterion_and_correction (criterion_id, correction_id), 
                PRIMARY KEY(id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_document (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_node_id INT DEFAULT NULL, 
                drop_id INT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_1C357F0C1BAD783F (resource_node_id), 
                INDEX IDX_1C357F0C4D224760 (drop_id), 
                PRIMARY KEY(id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_correction (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                drop_id INT DEFAULT NULL, 
                drop_zone_id INT NOT NULL, 
                total_grade NUMERIC(10, 2) DEFAULT NULL, 
                comment TEXT DEFAULT NULL, 
                valid BOOLEAN NOT NULL, 
                start_date DATETIME NOT NULL, 
                last_open_date DATETIME NOT NULL, 
                end_date DATETIME DEFAULT NULL, 
                finished BOOLEAN NOT NULL, 
                editable BOOLEAN NOT NULL, 
                reporter BOOLEAN NOT NULL, 
                reportComment TEXT DEFAULT NULL, 
                correctionDenied BOOLEAN NOT NULL, 
                correctionDeniedComment TEXT DEFAULT NULL, 
                INDEX IDX_BA9AF20BA76ED395 (user_id), 
                INDEX IDX_BA9AF20B4D224760 (drop_id), 
                INDEX IDX_BA9AF20BA8C6E7BD (drop_zone_id), 
                PRIMARY KEY(id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_dropzone (
                id INT AUTO_INCREMENT NOT NULL, 
                hidden_directory_id INT DEFAULT NULL, 
                event_agenda_drop INT DEFAULT NULL, 
                event_agenda_correction INT DEFAULT NULL, 
                edition_state INT NOT NULL, 
                instruction TEXT DEFAULT NULL, 
                correction_instruction TEXT DEFAULT NULL, 
                success_message TEXT DEFAULT NULL, 
                fail_message TEXT DEFAULT NULL, 
                allow_workspace_resource BOOLEAN NOT NULL, 
                allow_upload BOOLEAN NOT NULL, 
                allow_url BOOLEAN NOT NULL, 
                allow_rich_text BOOLEAN NOT NULL, 
                peer_review BOOLEAN NOT NULL, 
                expected_total_correction INT NOT NULL, 
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
                total_criteria_column INT NOT NULL, 
                auto_close_opened_drops_when_time_is_up BOOLEAN DEFAULT 'false' NOT NULL, 
                auto_close_state VARCHAR(255) DEFAULT 'waiting' NOT NULL, 
                notify_on_drop BOOLEAN DEFAULT 'false' NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_FF7070B5342CDF (hidden_directory_id), 
                UNIQUE INDEX UNIQ_FF7070BE6B974D2 (event_agenda_drop), 
                UNIQUE INDEX UNIQ_FF7070B8D9E1321 (event_agenda_correction), 
                UNIQUE INDEX UNIQ_FF7070BB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_criterion 
            ADD CONSTRAINT FK_CC96E6A6A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_drop 
            ADD CONSTRAINT FK_71757239A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_drop 
            ADD CONSTRAINT FK_71757239A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_drop 
            ADD CONSTRAINT FK_717572395342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_grade 
            ADD CONSTRAINT FK_D33E07AF97766307 FOREIGN KEY (criterion_id) 
            REFERENCES innova_collecticielbundle_criterion (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_grade 
            ADD CONSTRAINT FK_D33E07AF94AE086B FOREIGN KEY (correction_id) 
            REFERENCES innova_collecticielbundle_correction (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            ADD CONSTRAINT FK_1C357F0C1BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            ADD CONSTRAINT FK_1C357F0C4D224760 FOREIGN KEY (drop_id) 
            REFERENCES innova_collecticielbundle_drop (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            ADD CONSTRAINT FK_BA9AF20BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            ADD CONSTRAINT FK_BA9AF20B4D224760 FOREIGN KEY (drop_id) 
            REFERENCES innova_collecticielbundle_drop (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            ADD CONSTRAINT FK_BA9AF20BA8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES innova_collecticielbundle_dropzone (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070B5342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070BE6B974D2 FOREIGN KEY (event_agenda_drop) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070B8D9E1321 FOREIGN KEY (event_agenda_correction) 
            REFERENCES claro_event (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_dropzone 
            ADD CONSTRAINT FK_FF7070BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_grade 
            DROP FOREIGN KEY FK_D33E07AF97766307
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            DROP FOREIGN KEY FK_1C357F0C4D224760
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            DROP FOREIGN KEY FK_BA9AF20B4D224760
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_grade 
            DROP FOREIGN KEY FK_D33E07AF94AE086B
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_criterion 
            DROP FOREIGN KEY FK_CC96E6A6A8C6E7BD
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_drop 
            DROP FOREIGN KEY FK_71757239A8C6E7BD
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_correction 
            DROP FOREIGN KEY FK_BA9AF20BA8C6E7BD
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
