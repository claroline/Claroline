<?php

namespace Claroline\AudioPlayerBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/05/24 11:45:13
 */
class Version20190524114511 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_audio_resource_section (
                id INT AUTO_INCREMENT NOT NULL, 
                node_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
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
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_67587B08D17F50A6 (uuid), 
                INDEX IDX_67587B08460D9FD7 (node_id), 
                INDEX IDX_67587B08A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_audio_params (
                id INT AUTO_INCREMENT NOT NULL, 
                node_id INT NOT NULL, 
                sections_type VARCHAR(255) NOT NULL, 
                rate_control TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_B7FF82AAD17F50A6 (uuid), 
                INDEX IDX_B7FF82AA460D9FD7 (node_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
        $this->addSql('
            ALTER TABLE claro_audio_params 
            ADD CONSTRAINT FK_B7FF82AA460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_audio_section CHANGE score score DOUBLE PRECISION DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_audio_resource_section_comment 
            DROP FOREIGN KEY FK_16790DB2D823E37A
        ');
        $this->addSql('
            DROP TABLE claro_audio_resource_section
        ');
        $this->addSql('
            DROP TABLE claro_audio_resource_section_comment
        ');
        $this->addSql('
            DROP TABLE claro_audio_params
        ');
        $this->addSql('
            ALTER TABLE claro_audio_section CHANGE score score DOUBLE PRECISION NOT NULL
        ');
    }
}
