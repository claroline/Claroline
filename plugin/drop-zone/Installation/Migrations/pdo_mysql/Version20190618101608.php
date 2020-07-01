<?php

namespace Claroline\DropZoneBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:38:30
 */
class Version20190618101608 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_revision (
                id INT AUTO_INCREMENT NOT NULL, 
                drop_id INT NOT NULL, 
                creator_id INT DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_5D4C5512D17F50A6 (uuid), 
                INDEX IDX_5D4C55124D224760 (drop_id), 
                INDEX IDX_5D4C551261220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_drop (
                id INT AUTO_INCREMENT NOT NULL, 
                dropzone_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                drop_date DATETIME DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                reported TINYINT(1) NOT NULL, 
                finished TINYINT(1) NOT NULL, 
                drop_number INT DEFAULT NULL, 
                auto_closed_drop TINYINT(1) NOT NULL, 
                unlocked_drop TINYINT(1) NOT NULL, 
                unlocked_user TINYINT(1) NOT NULL, 
                team_id INT DEFAULT NULL, 
                team_uuid VARCHAR(255) DEFAULT NULL, 
                team_name VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_97D5DB31D17F50A6 (uuid), 
                INDEX IDX_97D5DB3154FC3EC3 (dropzone_id), 
                INDEX IDX_97D5DB31A76ED395 (user_id), 
                UNIQUE INDEX dropzone_drop_unique_dropzone_team (dropzone_id, team_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_drop_users (
                drop_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_466E34EB4D224760 (drop_id), 
                INDEX IDX_466E34EBA76ED395 (user_id), 
                PRIMARY KEY(drop_id, user_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_dropzonebundle_document (
                id INT AUTO_INCREMENT NOT NULL, 
                drop_id INT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                revision_id INT DEFAULT NULL, 
                document_type LONGTEXT NOT NULL, 
                file_array LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                url VARCHAR(255) DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                drop_date DATETIME NOT NULL, 
                is_manager TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_E846CAA8D17F50A6 (uuid), 
                INDEX IDX_E846CAA84D224760 (drop_id), 
                INDEX IDX_E846CAA889329D25 (resource_id), 
                INDEX IDX_E846CAA8A76ED395 (user_id), 
                INDEX IDX_E846CAA81DFA7C8F (revision_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_revision_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                revision_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_1756823AD17F50A6 (uuid), 
                INDEX IDX_1756823A1DFA7C8F (revision_id), 
                INDEX IDX_1756823AA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_dropzone (
                id INT AUTO_INCREMENT NOT NULL, 
                edition_state SMALLINT NOT NULL, 
                instruction LONGTEXT DEFAULT NULL, 
                correction_instruction LONGTEXT DEFAULT NULL, 
                success_message LONGTEXT DEFAULT NULL, 
                fail_message LONGTEXT DEFAULT NULL, 
                workspace_resource_enabled TINYINT(1) NOT NULL, 
                upload_enabled TINYINT(1) NOT NULL, 
                url_enabled TINYINT(1) NOT NULL, 
                rich_text_enabled TINYINT(1) NOT NULL, 
                peer_review TINYINT(1) NOT NULL, 
                expected_correction_total SMALLINT NOT NULL, 
                display_notation_to_learners TINYINT(1) NOT NULL, 
                display_notation_message_to_learners TINYINT(1) NOT NULL, 
                score_to_pass DOUBLE PRECISION NOT NULL, 
                score_max INT NOT NULL, 
                drop_type LONGTEXT NOT NULL, 
                manual_planning TINYINT(1) NOT NULL, 
                manual_state LONGTEXT NOT NULL, 
                drop_start_date DATETIME DEFAULT NULL, 
                drop_end_date DATETIME DEFAULT NULL, 
                review_start_date DATETIME DEFAULT NULL, 
                review_end_date DATETIME DEFAULT NULL, 
                comment_in_correction_enabled TINYINT(1) NOT NULL, 
                comment_in_correction_forced TINYINT(1) NOT NULL, 
                display_corrections_to_learners TINYINT(1) NOT NULL, 
                correction_denial_enabled TINYINT(1) NOT NULL, 
                criteria_enabled TINYINT(1) NOT NULL, 
                criteria_total SMALLINT NOT NULL, 
                auto_close_drops_at_drop_end_date TINYINT(1) NOT NULL, 
                auto_close_state INT NOT NULL, 
                drop_closed TINYINT(1) NOT NULL, 
                notify_on_drop TINYINT(1) NOT NULL, 
                corrector_displayed TINYINT(1) NOT NULL, 
                revision_enabled TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_FB84B2AFD17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_FB84B2AFB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_correction (
                id INT AUTO_INCREMENT NOT NULL, 
                drop_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                correction_comment LONGTEXT DEFAULT NULL, 
                is_valid TINYINT(1) NOT NULL, 
                start_date DATETIME NOT NULL, 
                last_edition_date DATETIME NOT NULL, 
                end_date DATETIME DEFAULT NULL, 
                finished TINYINT(1) NOT NULL, 
                editable TINYINT(1) NOT NULL, 
                reported TINYINT(1) NOT NULL, 
                reported_comment LONGTEXT DEFAULT NULL, 
                correction_denied TINYINT(1) NOT NULL, 
                correction_denied_comment LONGTEXT DEFAULT NULL, 
                team_id INT DEFAULT NULL, 
                team_uuid VARCHAR(255) DEFAULT NULL, 
                team_name VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_CBFA3896D17F50A6 (uuid), 
                INDEX IDX_CBFA38964D224760 (drop_id), 
                INDEX IDX_CBFA3896A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_drop_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                drop_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_214AC1D1D17F50A6 (uuid), 
                INDEX IDX_214AC1D14D224760 (drop_id), 
                INDEX IDX_214AC1D1A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_grade (
                id INT AUTO_INCREMENT NOT NULL, 
                correction_id INT NOT NULL, 
                criterion_id INT NOT NULL, 
                grade_value INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DD032F34D17F50A6 (uuid), 
                INDEX IDX_DD032F3494AE086B (correction_id), 
                INDEX IDX_DD032F3497766307 (criterion_id), 
                UNIQUE INDEX unique_grade_for_criterion_and_correction (criterion_id, correction_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_dropzonebundle_criterion (
                id INT AUTO_INCREMENT NOT NULL, 
                dropzone_id INT NOT NULL, 
                instruction LONGTEXT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_1DD9F2E2D17F50A6 (uuid), 
                INDEX IDX_1DD9F2E254FC3EC3 (dropzone_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_dropzonebundle_tool (
                id INT AUTO_INCREMENT NOT NULL, 
                tool_name VARCHAR(255) NOT NULL, 
                tool_type INT NOT NULL, 
                tool_drop_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_C733E0C2D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_dropzonebundle_tool_document (
                id INT AUTO_INCREMENT NOT NULL, 
                document_id INT NOT NULL, 
                tool_id INT NOT NULL, 
                tool_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_762E507AD17F50A6 (uuid), 
                INDEX IDX_762E507AC33F7837 (document_id), 
                INDEX IDX_762E507A8F7B22CC (tool_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision 
            ADD CONSTRAINT FK_5D4C55124D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_dropzonebundle_drop (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision 
            ADD CONSTRAINT FK_5D4C551261220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop 
            ADD CONSTRAINT FK_97D5DB3154FC3EC3 FOREIGN KEY (dropzone_id) 
            REFERENCES claro_dropzonebundle_dropzone (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop 
            ADD CONSTRAINT FK_97D5DB31A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop_users 
            ADD CONSTRAINT FK_466E34EB4D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_dropzonebundle_drop (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop_users 
            ADD CONSTRAINT FK_466E34EBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            ADD CONSTRAINT FK_E846CAA84D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_dropzonebundle_drop (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            ADD CONSTRAINT FK_E846CAA889329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            ADD CONSTRAINT FK_E846CAA8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            ADD CONSTRAINT FK_E846CAA81DFA7C8F FOREIGN KEY (revision_id) 
            REFERENCES claro_dropzonebundle_revision (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision_comment 
            ADD CONSTRAINT FK_1756823A1DFA7C8F FOREIGN KEY (revision_id) 
            REFERENCES claro_dropzonebundle_revision (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision_comment 
            ADD CONSTRAINT FK_1756823AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_dropzone 
            ADD CONSTRAINT FK_FB84B2AFB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_correction 
            ADD CONSTRAINT FK_CBFA38964D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_dropzonebundle_drop (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_correction 
            ADD CONSTRAINT FK_CBFA3896A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop_comment 
            ADD CONSTRAINT FK_214AC1D14D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_dropzonebundle_drop (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop_comment 
            ADD CONSTRAINT FK_214AC1D1A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_grade 
            ADD CONSTRAINT FK_DD032F3494AE086B FOREIGN KEY (correction_id) 
            REFERENCES claro_dropzonebundle_correction (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_grade 
            ADD CONSTRAINT FK_DD032F3497766307 FOREIGN KEY (criterion_id) 
            REFERENCES claro_dropzonebundle_criterion (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_criterion 
            ADD CONSTRAINT FK_1DD9F2E254FC3EC3 FOREIGN KEY (dropzone_id) 
            REFERENCES claro_dropzonebundle_dropzone (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_tool_document 
            ADD CONSTRAINT FK_762E507AC33F7837 FOREIGN KEY (document_id) 
            REFERENCES claro_dropzonebundle_document (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_tool_document 
            ADD CONSTRAINT FK_762E507A8F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_dropzonebundle_tool (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            DROP FOREIGN KEY FK_E846CAA81DFA7C8F
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision_comment 
            DROP FOREIGN KEY FK_1756823A1DFA7C8F
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_revision 
            DROP FOREIGN KEY FK_5D4C55124D224760
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop_users 
            DROP FOREIGN KEY FK_466E34EB4D224760
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_document 
            DROP FOREIGN KEY FK_E846CAA84D224760
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_correction 
            DROP FOREIGN KEY FK_CBFA38964D224760
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop_comment 
            DROP FOREIGN KEY FK_214AC1D14D224760
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_tool_document 
            DROP FOREIGN KEY FK_762E507AC33F7837
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_drop 
            DROP FOREIGN KEY FK_97D5DB3154FC3EC3
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_criterion 
            DROP FOREIGN KEY FK_1DD9F2E254FC3EC3
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_grade 
            DROP FOREIGN KEY FK_DD032F3494AE086B
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_grade 
            DROP FOREIGN KEY FK_DD032F3497766307
        ');
        $this->addSql('
            ALTER TABLE claro_dropzonebundle_tool_document 
            DROP FOREIGN KEY FK_762E507A8F7B22CC
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_revision
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_drop
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_drop_users
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_document
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_revision_comment
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_dropzone
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_correction
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_drop_comment
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_grade
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_criterion
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_tool
        ');
        $this->addSql('
            DROP TABLE claro_dropzonebundle_tool_document
        ');
    }
}
