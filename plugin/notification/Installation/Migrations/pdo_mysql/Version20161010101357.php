<?php

namespace Icap\NotificationBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:27:11
 */
class Version20161010101357 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__notification_follower_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                hash VARCHAR(64) NOT NULL, 
                resource_class VARCHAR(255) NOT NULL, 
                resource_id INT NOT NULL, 
                follower_id INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE icap__notification (
                id INT AUTO_INCREMENT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                user_id INT DEFAULT NULL, 
                resource_id INT DEFAULT NULL, 
                icon_key VARCHAR(255) DEFAULT NULL, 
                action_key VARCHAR(255) NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE icap__notification_plugin_configuration (
                id INT AUTO_INCREMENT NOT NULL, 
                dropdown_items INT NOT NULL, 
                max_per_page INT NOT NULL, 
                purge_enabled TINYINT(1) NOT NULL, 
                purge_after_days INT NOT NULL, 
                last_purge_date DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE icap__notification_user_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                display_enabled_types LONGTEXT NOT NULL COMMENT '(DC2Type:array)', 
                rss_enabled_types LONGTEXT NOT NULL COMMENT '(DC2Type:array)', 
                rss_id VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_F44A756DA9D08426 (rss_id), 
                INDEX IDX_F44A756DA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE icap__notification_viewer (
                id INT AUTO_INCREMENT NOT NULL, 
                notification_id INT NOT NULL, 
                viewer_id INT NOT NULL, 
                status TINYINT(1) DEFAULT NULL, 
                INDEX IDX_DB60418BEF1A9D84 (notification_id), 
                INDEX viewer_idx (viewer_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE icap__notification_user_parameters 
            ADD CONSTRAINT FK_F44A756DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE icap__notification_viewer 
            ADD CONSTRAINT FK_DB60418BEF1A9D84 FOREIGN KEY (notification_id) 
            REFERENCES icap__notification (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__notification_viewer 
            DROP FOREIGN KEY FK_DB60418BEF1A9D84
        ');
        $this->addSql('
            DROP TABLE icap__notification_follower_resource
        ');
        $this->addSql('
            DROP TABLE icap__notification
        ');
        $this->addSql('
            DROP TABLE icap__notification_plugin_configuration
        ');
        $this->addSql('
            DROP TABLE icap__notification_user_parameters
        ');
        $this->addSql('
            DROP TABLE icap__notification_viewer
        ');
    }
}
