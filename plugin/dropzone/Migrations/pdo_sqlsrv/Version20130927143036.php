<?php

namespace Icap\DropzoneBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/27 02:30:38
 */
class Version20130927143036 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_correction (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                drop_id INT, 
                drop_zone_id INT NOT NULL, 
                total_grade NUMERIC(10, 2), 
                comment VARCHAR(MAX), 
                valid BIT NOT NULL, 
                start_date DATETIME2(6) NOT NULL, 
                last_open_date DATETIME2(6) NOT NULL, 
                end_date DATETIME2(6), 
                finished BIT NOT NULL, 
                editable BIT NOT NULL, 
                reporter BIT NOT NULL, 
                reportComment VARCHAR(MAX), 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                drop_zone_id INT NOT NULL, 
                instruction VARCHAR(MAX) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F94B3BA7A8C6E7BD ON icap__dropzonebundle_criterion (drop_zone_id)
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_document (
                id INT IDENTITY NOT NULL, 
                resource_node_id INT, 
                drop_id INT NOT NULL, 
                type NVARCHAR(255) NOT NULL, 
                url NVARCHAR(255), 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                drop_zone_id INT NOT NULL, 
                user_id INT NOT NULL, 
                hidden_directory_id INT, 
                drop_date DATETIME2(6) NOT NULL, 
                reported BIT NOT NULL, 
                finished BIT NOT NULL, 
                number INT NOT NULL, 
                PRIMARY KEY (id)
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
            WHERE hidden_directory_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_for_user_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, user_id) 
            WHERE drop_zone_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_number_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, number) 
            WHERE drop_zone_id IS NOT NULL 
            AND number IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_dropzone (
                id INT IDENTITY NOT NULL, 
                hidden_directory_id INT, 
                edition_state SMALLINT NOT NULL, 
                instruction VARCHAR(MAX), 
                allow_workspace_resource BIT NOT NULL, 
                allow_upload BIT NOT NULL, 
                allow_url BIT NOT NULL, 
                allow_rich_text BIT NOT NULL, 
                peer_review BIT NOT NULL, 
                expected_total_correction SMALLINT NOT NULL, 
                display_notation_to_learners BIT NOT NULL, 
                display_notation_message_to_learners BIT NOT NULL, 
                minimum_score_to_pass DOUBLE PRECISION NOT NULL, 
                manual_planning BIT NOT NULL, 
                manual_state NVARCHAR(255) NOT NULL, 
                start_allow_drop DATETIME2(6), 
                end_allow_drop DATETIME2(6), 
                start_review DATETIME2(6), 
                end_review DATETIME2(6), 
                allow_comment_in_correction BIT NOT NULL, 
                total_criteria_column SMALLINT NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC235342CDF ON icap__dropzonebundle_dropzone (hidden_directory_id) 
            WHERE hidden_directory_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_6782FC23B87FAB32 ON icap__dropzonebundle_dropzone (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_grade (
                id INT IDENTITY NOT NULL, 
                criterion_id INT NOT NULL, 
                correction_id INT NOT NULL, 
                value SMALLINT NOT NULL, 
                PRIMARY KEY (id)
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
            WHERE criterion_id IS NOT NULL 
            AND correction_id IS NOT NULL
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