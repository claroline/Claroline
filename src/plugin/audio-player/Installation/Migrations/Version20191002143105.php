<?php

namespace Claroline\AudioPlayerBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 01:49:53
 */
final class Version20191002143105 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_audio_resource_section (
                id INT AUTO_INCREMENT NOT NULL, 
                node_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                section_start DOUBLE PRECISION NOT NULL, 
                section_end DOUBLE PRECISION NOT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                section_type VARCHAR(255) NOT NULL, 
                show_transcript TINYINT(1) NOT NULL, 
                transcript LONGTEXT DEFAULT NULL, 
                comments_allowed TINYINT(1) NOT NULL, 
                show_help TINYINT(1) NOT NULL, 
                help LONGTEXT DEFAULT NULL, 
                show_audio TINYINT(1) NOT NULL, 
                audio_url VARCHAR(255) DEFAULT NULL, 
                audio_description VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_67587B08D17F50A6 (uuid), 
                INDEX IDX_67587B08460D9FD7 (node_id), 
                INDEX IDX_67587B08A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_audio_interaction_waveform (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                url VARCHAR(255) NOT NULL, 
                tolerance DOUBLE PRECISION NOT NULL, 
                answers_limit INT NOT NULL, 
                penalty DOUBLE PRECISION NOT NULL, 
                UNIQUE INDEX UNIQ_856E5BF91E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_audio_section (
                id INT AUTO_INCREMENT NOT NULL, 
                waveform_id INT DEFAULT NULL, 
                section_start DOUBLE PRECISION NOT NULL, 
                section_end DOUBLE PRECISION NOT NULL, 
                start_tolerance DOUBLE PRECISION NOT NULL, 
                end_tolerance DOUBLE PRECISION NOT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_3FFCA233D17F50A6 (uuid), 
                INDEX IDX_3FFCA2335B93C951 (waveform_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_audio_params (
                id INT AUTO_INCREMENT NOT NULL, 
                node_id INT NOT NULL, 
                sections_type VARCHAR(255) NOT NULL, 
                rate_control TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B7FF82AAD17F50A6 (uuid), 
                INDEX IDX_B7FF82AA460D9FD7 (node_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_audio_resource_section_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                section_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_16790DB2D17F50A6 (uuid), 
                INDEX IDX_16790DB2D823E37A (section_id), 
                INDEX IDX_16790DB2A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_audio_resource_section 
            ADD CONSTRAINT FK_67587B08460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_audio_resource_section 
            ADD CONSTRAINT FK_67587B08A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_audio_interaction_waveform 
            ADD CONSTRAINT FK_856E5BF91E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_audio_section 
            ADD CONSTRAINT FK_3FFCA2335B93C951 FOREIGN KEY (waveform_id) 
            REFERENCES claro_audio_interaction_waveform (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_audio_params 
            ADD CONSTRAINT FK_B7FF82AA460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_audio_resource_section_comment 
            ADD CONSTRAINT FK_16790DB2D823E37A FOREIGN KEY (section_id) 
            REFERENCES claro_audio_resource_section (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_audio_resource_section_comment 
            ADD CONSTRAINT FK_16790DB2A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_audio_resource_section 
            DROP FOREIGN KEY FK_67587B08460D9FD7
        ');
        $this->addSql('
            ALTER TABLE claro_audio_resource_section 
            DROP FOREIGN KEY FK_67587B08A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_audio_interaction_waveform 
            DROP FOREIGN KEY FK_856E5BF91E27F6BF
        ');
        $this->addSql('
            ALTER TABLE claro_audio_section 
            DROP FOREIGN KEY FK_3FFCA2335B93C951
        ');
        $this->addSql('
            ALTER TABLE claro_audio_params 
            DROP FOREIGN KEY FK_B7FF82AA460D9FD7
        ');
        $this->addSql('
            ALTER TABLE claro_audio_resource_section_comment 
            DROP FOREIGN KEY FK_16790DB2D823E37A
        ');
        $this->addSql('
            ALTER TABLE claro_audio_resource_section_comment 
            DROP FOREIGN KEY FK_16790DB2A76ED395
        ');
        $this->addSql('
            DROP TABLE claro_audio_resource_section
        ');
        $this->addSql('
            DROP TABLE claro_audio_interaction_waveform
        ');
        $this->addSql('
            DROP TABLE claro_audio_section
        ');
        $this->addSql('
            DROP TABLE claro_audio_params
        ');
        $this->addSql('
            DROP TABLE claro_audio_resource_section_comment
        ');
    }
}
