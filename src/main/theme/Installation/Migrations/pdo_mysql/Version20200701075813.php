<?php

namespace Claroline\ThemeBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 07:58:42
 */
class Version20200701075813 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        // because tables were originally created by CoreBundle
        $this->addSql('
            DROP TABLE IF EXISTS claro_theme_color_collection
        ');
        $this->addSql('
            DROP TABLE IF EXISTS claro_theme_poster_collection
        ');

        $this->addSql('
            CREATE TABLE claro_theme_color_collection (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                name LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_D195EAE6D17F50A6 (uuid), 
                INDEX IDX_D195EAE6A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_theme_poster_collection (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                name LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_2CD4B836D17F50A6 (uuid), 
                INDEX IDX_2CD4B836A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_theme_color_collection 
            ADD CONSTRAINT FK_D195EAE6A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_theme_poster_collection 
            ADD CONSTRAINT FK_2CD4B836A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');

        if (!$this->checkTableExists('claro_theme', $this->connection)) {
            $this->addSql('
                CREATE TABLE claro_theme (
                    id INT AUTO_INCREMENT NOT NULL, 
                    plugin_id INT DEFAULT NULL, 
                    user_id INT DEFAULT NULL, 
                    name VARCHAR(255) NOT NULL, 
                    description LONGTEXT DEFAULT NULL, 
                    enabled TINYINT(1) NOT NULL, 
                    is_default TINYINT(1) NOT NULL, 
                    extending_default TINYINT(1) NOT NULL, 
                    uuid VARCHAR(36) NOT NULL, 
                    UNIQUE INDEX UNIQ_1D76301AD17F50A6 (uuid), 
                    INDEX IDX_1D76301AEC942BCF (plugin_id), 
                    INDEX IDX_1D76301AA76ED395 (user_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
            ');
            $this->addSql('
                ALTER TABLE claro_theme 
                ADD CONSTRAINT FK_1D76301AEC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE
            ');
            $this->addSql('
                ALTER TABLE claro_theme 
                ADD CONSTRAINT FK_1D76301AA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE
            ');
        }

        if (!$this->checkTableExists('claro_icon_set', $this->connection)) {
            $this->addSql("
                CREATE TABLE claro_icon_set (
                    id INT AUTO_INCREMENT NOT NULL, 
                    name VARCHAR(255) NOT NULL, 
                    cname VARCHAR(255) NOT NULL, 
                    is_default TINYINT(1) NOT NULL, 
                    is_active TINYINT(1) NOT NULL, 
                    type VARCHAR(255) DEFAULT NULL, 
                    editable TINYINT(1) DEFAULT '0' NOT NULL, 
                    uuid VARCHAR(36) NOT NULL, 
                    UNIQUE INDEX UNIQ_D91D0EE67D75B9A (cname), 
                    UNIQUE INDEX UNIQ_D91D0EED17F50A6 (uuid), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
            ");
        }

        if (!$this->checkTableExists('claro_icon_item', $this->connection)) {
            $this->addSql('
                CREATE TABLE claro_icon_item (
                    id INT AUTO_INCREMENT NOT NULL, 
                    icon_set_id INT DEFAULT NULL, 
                    name VARCHAR(255) DEFAULT NULL, 
                    class VARCHAR(255) DEFAULT NULL, 
                    mime_type VARCHAR(255) DEFAULT NULL, 
                    relative_url VARCHAR(255) NOT NULL, 
                    uuid VARCHAR(36) NOT NULL, 
                    UNIQUE INDEX UNIQ_D727F16BD17F50A6 (uuid), 
                    INDEX IDX_D727F16B48D16F3B (icon_set_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
            ');
            $this->addSql('
                ALTER TABLE claro_icon_item 
                ADD CONSTRAINT FK_D727F16B48D16F3B FOREIGN KEY (icon_set_id) 
                REFERENCES claro_icon_set (id) 
                ON DELETE CASCADE
            ');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_theme_color_collection
        ');
        $this->addSql('
            DROP TABLE claro_theme_poster_collection
        ');

        if ($this->checkTableExists('claro_theme', $this->connection)) {
            $this->addSql('
                ALTER TABLE claro_theme 
                DROP FOREIGN KEY FK_1D76301AA76ED395
            ');
            $this->addSql('
                ALTER TABLE claro_theme 
                DROP FOREIGN KEY FK_1D76301AEC942BCF
            ');
            $this->addSql('
                DROP TABLE claro_theme
            ');
        }

        if ($this->checkTableExists('claro_icon_item', $this->connection)) {
            $this->addSql('
                ALTER TABLE claro_icon_item 
                DROP FOREIGN KEY FK_D727F16B48D16F3B
            ');
            $this->addSql('
                DROP TABLE claro_icon_item
            ');
        }

        if ($this->checkTableExists('claro_icon_set', $this->connection)) {
            $this->addSql('
                DROP TABLE claro_icon_set
            ');
        }
    }
}
