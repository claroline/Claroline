<?php

namespace Claroline\ThemeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 09:44:53
 */
final class Version20230426080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_theme (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                is_default TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_1D76301AD17F50A6 (uuid), 
                INDEX IDX_1D76301AEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            CREATE TABLE claro_icon_item (
                id INT AUTO_INCREMENT NOT NULL, 
                icon_set_id INT DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                relative_url VARCHAR(255) NOT NULL, 
                svg TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_D727F16BD17F50A6 (uuid), 
                INDEX IDX_D727F16B48D16F3B (icon_set_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_icon_set (
                id INT AUTO_INCREMENT NOT NULL, 
                cname VARCHAR(255) NOT NULL, 
                is_default TINYINT(1) DEFAULT 0 NOT NULL, 
                type VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_D91D0EE67D75B9A (cname), 
                UNIQUE INDEX UNIQ_D91D0EED17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_theme 
            ADD CONSTRAINT FK_1D76301AEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item 
            ADD CONSTRAINT FK_D727F16B48D16F3B FOREIGN KEY (icon_set_id) 
            REFERENCES claro_icon_set (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_theme 
            DROP FOREIGN KEY FK_1D76301AEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_icon_item 
            DROP FOREIGN KEY FK_D727F16B48D16F3B
        ');
        $this->addSql('
            DROP TABLE claro_theme
        ');
        $this->addSql('
            DROP TABLE claro_icon_item
        ');
        $this->addSql('
            DROP TABLE claro_icon_set
        ');
    }
}
