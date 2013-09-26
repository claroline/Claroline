<?php

namespace Icap\DropzoneBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/26 11:23:37
 */
class Version20130926112335 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_correction (
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
                PRIMARY KEY(id), 
                INDEX IDX_CDA81F40A76ED395 (user_id), 
                INDEX IDX_CDA81F404D224760 (drop_id), 
                INDEX IDX_CDA81F40A8C6E7BD (drop_zone_id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_criterion (
                id INT AUTO_INCREMENT NOT NULL, 
                drop_zone_id INT NOT NULL, 
                instruction TEXT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_F94B3BA7A8C6E7BD (drop_zone_id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_document (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_node_id INT DEFAULT NULL, 
                drop_id INT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_744084241BAD783F (resource_node_id), 
                INDEX IDX_744084244D224760 (drop_id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_drop (
                id INT AUTO_INCREMENT NOT NULL, 
                drop_zone_id INT NOT NULL, 
                user_id INT NOT NULL, 
                hidden_directory_id INT DEFAULT NULL, 
                drop_date DATETIME NOT NULL, 
                reported BOOLEAN NOT NULL, 
                finished BOOLEAN NOT NULL, 
                number INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_3AD19BA6A8C6E7BD (drop_zone_id), 
                INDEX IDX_3AD19BA6A76ED395 (user_id), 
                UNIQUE INDEX UNIQ_3AD19BA65342CDF (hidden_directory_id), 
                UNIQUE INDEX unique_drop_for_user_in_drop_zone (drop_zone_id, user_id), 
                UNIQUE INDEX unique_drop_number_in_drop_zone (drop_zone_id, number)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_dropzone (
                id INT AUTO_INCREMENT NOT NULL, 
                hidden_directory_id INT DEFAULT NULL, 
                edition_state INT NOT NULL, 
                instruction TEXT DEFAULT NULL, 
                allow_workspace_resource BOOLEAN NOT NULL, 
                allow_upload BOOLEAN NOT NULL, 
                allow_url BOOLEAN NOT NULL, 
                allow_rich_text BOOLEAN NOT NULL, 
                peer_review BOOLEAN NOT NULL, 
                expected_total_correction INT NOT NULL, 
                display_notation_to_learners BOOLEAN NOT NULL, 
                display_notation_message_to_learners BOOLEAN NOT NULL, 
                minimum_score_to_pass INT NOT NULL, 
                manual_planning BOOLEAN NOT NULL, 
                manual_state VARCHAR(255) NOT NULL, 
                start_allow_drop DATETIME DEFAULT NULL, 
                end_allow_drop DATETIME DEFAULT NULL, 
                start_review DATETIME DEFAULT NULL, 
                end_review DATETIME DEFAULT NULL, 
                allow_comment_in_correction BOOLEAN NOT NULL, 
                total_criteria_column INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_6782FC235342CDF (hidden_directory_id), 
                UNIQUE INDEX UNIQ_6782FC23B87FAB32 (resourceNode_id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_grade (
                id INT AUTO_INCREMENT NOT NULL, 
                criterion_id INT NOT NULL, 
                correction_id INT NOT NULL, 
                `value` INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_B3C52D9397766307 (criterion_id), 
                INDEX IDX_B3C52D9394AE086B (correction_id), 
                UNIQUE INDEX unique_grade_for_criterion_and_correction (criterion_id, correction_id)
            )
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD CONSTRAINT FK_CDA81F40A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD CONSTRAINT FK_CDA81F404D224760 FOREIGN KEY (drop_id) 
            REFERENCES icap__dropzonebundle_drop (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD CONSTRAINT FK_CDA81F40A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES icap__dropzonebundle_dropzone (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_criterion 
            ADD CONSTRAINT FK_F94B3BA7A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES icap__dropzonebundle_dropzone (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_document 
            ADD CONSTRAINT FK_744084241BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_document 
            ADD CONSTRAINT FK_744084244D224760 FOREIGN KEY (drop_id) 
            REFERENCES icap__dropzonebundle_drop (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT FK_3AD19BA6A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES icap__dropzonebundle_dropzone (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT FK_3AD19BA6A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT FK_3AD19BA65342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC235342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC23B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            ADD CONSTRAINT FK_B3C52D9397766307 FOREIGN KEY (criterion_id) 
            REFERENCES icap__dropzonebundle_criterion (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            ADD CONSTRAINT FK_B3C52D9394AE086B FOREIGN KEY (correction_id) 
            REFERENCES icap__dropzonebundle_correction (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            DROP FOREIGN KEY FK_B3C52D9394AE086B
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            DROP FOREIGN KEY FK_B3C52D9397766307
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP FOREIGN KEY FK_CDA81F404D224760
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_document 
            DROP FOREIGN KEY FK_744084244D224760
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP FOREIGN KEY FK_CDA81F40A8C6E7BD
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_criterion 
            DROP FOREIGN KEY FK_F94B3BA7A8C6E7BD
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            DROP FOREIGN KEY FK_3AD19BA6A8C6E7BD
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_correction
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_criterion
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_document
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_drop
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_dropzone
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_grade
        ");
    }
}