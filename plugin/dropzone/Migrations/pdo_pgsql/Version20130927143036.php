<?php

namespace Icap\DropzoneBundle\Migrations\pdo_pgsql;

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
            CREATE TABLE icap__dropzonebundle_correction (
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
                PRIMARY KEY(id)
            )
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
            CREATE TABLE icap__dropzonebundle_criterion (
                id SERIAL NOT NULL, 
                drop_zone_id INT NOT NULL, 
                instruction TEXT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F94B3BA7A8C6E7BD ON icap__dropzonebundle_criterion (drop_zone_id)
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_document (
                id SERIAL NOT NULL, 
                resource_node_id INT DEFAULT NULL, 
                drop_id INT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_744084241BAD783F ON icap__dropzonebundle_document (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_744084244D224760 ON icap__dropzonebundle_document (drop_id)
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_drop (
                id SERIAL NOT NULL, 
                drop_zone_id INT NOT NULL, 
                user_id INT NOT NULL, 
                hidden_directory_id INT DEFAULT NULL, 
                drop_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                reported BOOLEAN NOT NULL, 
                finished BOOLEAN NOT NULL, 
                number INT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3AD19BA6A8C6E7BD ON icap__dropzonebundle_drop (drop_zone_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3AD19BA6A76ED395 ON icap__dropzonebundle_drop (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3AD19BA65342CDF ON icap__dropzonebundle_drop (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_for_user_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_number_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, number)
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_dropzone (
                id SERIAL NOT NULL, 
                hidden_directory_id INT DEFAULT NULL, 
                edition_state SMALLINT NOT NULL, 
                instruction TEXT DEFAULT NULL, 
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
                total_criteria_column SMALLINT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC235342CDF ON icap__dropzonebundle_dropzone (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC23B87FAB32 ON icap__dropzonebundle_dropzone (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_grade (
                id SERIAL NOT NULL, 
                criterion_id INT NOT NULL, 
                correction_id INT NOT NULL, 
                value SMALLINT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B3C52D9397766307 ON icap__dropzonebundle_grade (criterion_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B3C52D9394AE086B ON icap__dropzonebundle_grade (correction_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_grade_for_criterion_and_correction ON icap__dropzonebundle_grade (criterion_id, correction_id)
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD CONSTRAINT FK_CDA81F40A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD CONSTRAINT FK_CDA81F404D224760 FOREIGN KEY (drop_id) 
            REFERENCES icap__dropzonebundle_drop (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            ADD CONSTRAINT FK_CDA81F40A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES icap__dropzonebundle_dropzone (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_criterion 
            ADD CONSTRAINT FK_F94B3BA7A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES icap__dropzonebundle_dropzone (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_document 
            ADD CONSTRAINT FK_744084241BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_document 
            ADD CONSTRAINT FK_744084244D224760 FOREIGN KEY (drop_id) 
            REFERENCES icap__dropzonebundle_drop (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT FK_3AD19BA6A8C6E7BD FOREIGN KEY (drop_zone_id) 
            REFERENCES icap__dropzonebundle_dropzone (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT FK_3AD19BA6A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD CONSTRAINT FK_3AD19BA65342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC235342CDF FOREIGN KEY (hidden_directory_id) 
            REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_dropzone 
            ADD CONSTRAINT FK_6782FC23B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            ADD CONSTRAINT FK_B3C52D9397766307 FOREIGN KEY (criterion_id) 
            REFERENCES icap__dropzonebundle_criterion (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            ADD CONSTRAINT FK_B3C52D9394AE086B FOREIGN KEY (correction_id) 
            REFERENCES icap__dropzonebundle_correction (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            DROP CONSTRAINT FK_B3C52D9394AE086B
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_grade 
            DROP CONSTRAINT FK_B3C52D9397766307
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP CONSTRAINT FK_CDA81F404D224760
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_document 
            DROP CONSTRAINT FK_744084244D224760
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_correction 
            DROP CONSTRAINT FK_CDA81F40A8C6E7BD
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_criterion 
            DROP CONSTRAINT FK_F94B3BA7A8C6E7BD
        ");
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            DROP CONSTRAINT FK_3AD19BA6A8C6E7BD
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