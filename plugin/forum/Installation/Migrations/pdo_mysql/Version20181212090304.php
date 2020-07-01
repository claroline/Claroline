<?php

namespace Claroline\ForumBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:40:37
 */
class Version20181212090304 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_forum_message (
                id INT AUTO_INCREMENT NOT NULL, 
                subject_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                moderation VARCHAR(255) NOT NULL, 
                flagged TINYINT(1) NOT NULL, 
                first TINYINT(1) NOT NULL, 
                content LONGTEXT NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                author VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_6A49AC0ED17F50A6 (uuid), 
                INDEX IDX_6A49AC0E23EDC87 (subject_id), 
                INDEX IDX_6A49AC0E727ACA70 (parent_id), 
                INDEX IDX_6A49AC0EA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_forum_subject (
                id INT AUTO_INCREMENT NOT NULL, 
                forum_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                poster_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                sticked TINYINT(1) NOT NULL, 
                closed TINYINT(1) NOT NULL, 
                flagged TINYINT(1) NOT NULL, 
                author VARCHAR(255) DEFAULT NULL, 
                viewCount INT NOT NULL, 
                moderation VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_273AA20BD17F50A6 (uuid), 
                INDEX IDX_273AA20B29CCBAD0 (forum_id), 
                INDEX IDX_273AA20BA76ED395 (user_id), 
                INDEX IDX_273AA20B5BB66C05 (poster_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_forum (
                id INT AUTO_INCREMENT NOT NULL, 
                validationMode VARCHAR(255) NOT NULL, 
                maxComment INT NOT NULL, 
                displayMessages INT NOT NULL, 
                dataListOptions VARCHAR(255) NOT NULL, 
                lockDate DATETIME DEFAULT NULL, 
                show_overview TINYINT(1) DEFAULT '1' NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_F2869DFD17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_F2869DFB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_forum_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                forum_id INT DEFAULT NULL, 
                access TINYINT(1) NOT NULL, 
                banned TINYINT(1) NOT NULL, 
                notified TINYINT(1) NOT NULL, 
                INDEX IDX_2CFBFDC4A76ED395 (user_id), 
                INDEX IDX_2CFBFDC429CCBAD0 (forum_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0E23EDC87 FOREIGN KEY (subject_id) 
            REFERENCES claro_forum_subject (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0E727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_forum_message (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B5BB66C05 FOREIGN KEY (poster_id) 
            REFERENCES claro_public_file (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_forum 
            ADD CONSTRAINT FK_F2869DFB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_forum_user 
            ADD CONSTRAINT FK_2CFBFDC4A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_forum_user 
            ADD CONSTRAINT FK_2CFBFDC429CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_forum_message 
            DROP FOREIGN KEY FK_6A49AC0E727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message 
            DROP FOREIGN KEY FK_6A49AC0E23EDC87
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject 
            DROP FOREIGN KEY FK_273AA20B29CCBAD0
        ');
        $this->addSql('
            ALTER TABLE claro_forum_user 
            DROP FOREIGN KEY FK_2CFBFDC429CCBAD0
        ');
        $this->addSql('
            DROP TABLE claro_forum_message
        ');
        $this->addSql('
            DROP TABLE claro_forum_subject
        ');
        $this->addSql('
            DROP TABLE claro_forum
        ');
        $this->addSql('
            DROP TABLE claro_forum_user
        ');
    }
}
