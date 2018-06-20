<?php

namespace Claroline\ForumBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/06/13 11:19:19
 */
class Version20180613111918 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
            ALTER TABLE claro_forum_user
            ADD CONSTRAINT FK_2CFBFDC4A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_forum_user
            ADD CONSTRAINT FK_2CFBFDC429CCBAD0 FOREIGN KEY (forum_id)
            REFERENCES claro_forum (id)
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject
            DROP FOREIGN KEY FK_273AA20B12469DE2
        ');
        $this->addSql('
            DROP INDEX IDX_273AA20B12469DE2 ON claro_forum_subject
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject
            ADD poster_id INT DEFAULT NULL,
            ADD content LONGTEXT NOT NULL,
            ADD sticked TINYINT(1) NOT NULL,
            ADD closed TINYINT(1) NOT NULL,
            ADD flagged TINYINT(1) NOT NULL,
            ADD viewCount INT NOT NULL,
            ADD uuid VARCHAR(36) NOT NULL,
            DROP isSticked,
            DROP isClosed,
            CHANGE category_id forum_id INT DEFAULT NULL
        ');
        $this->addSql('
            UPDATE claro_forum_subject SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            UPDATE claro_forum_subject SET content = ""
        ');
        $this->addSql('
            UPDATE claro_forum_subject SET sticked = false
        ');
        $this->addSql('
            UPDATE claro_forum_subject SET closed = false
        ');
        $this->addSql('
            UPDATE claro_forum_subject SET flagged = false
        ');
        $this->addSql('
            UPDATE claro_forum_subject SET viewCount = 5
        ');
        //might cause some issues but worth keeping
        /*
            ALTER TABLE claro_forum_subject
            ADD CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id)
            REFERENCES claro_forum (id)
            ON DELETE CASCADE
        */
        $this->addSql('
            ALTER TABLE claro_forum_subject
            ADD CONSTRAINT FK_273AA20B5BB66C05 FOREIGN KEY (poster_id)
            REFERENCES claro_public_file (id)
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_273AA20BD17F50A6 ON claro_forum_subject (uuid)
        ');
        $this->addSql('
            CREATE INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject (forum_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_273AA20B5BB66C05 ON claro_forum_subject (poster_id)
        ');
        $this->addSql("
            ALTER TABLE claro_forum
            ADD validationMode VARCHAR(255) NOT NULL,
            ADD maxComment INT NOT NULL,
            ADD displayMessages INT NOT NULL,
            ADD dataListOptions VARCHAR(255) NOT NULL,
            ADD lockDate DATETIME DEFAULT NULL,
            ADD show_overview TINYINT(1) DEFAULT '1' NOT NULL,
            ADD description LONGTEXT DEFAULT NULL,
            ADD uuid VARCHAR(36) NOT NULL,
            DROP activate_notifications
        ");
        $this->addSql('
            UPDATE claro_forum SET displayMessages = 5
        ');
        $this->addSql('
            UPDATE claro_forum SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_F2869DFD17F50A6 ON claro_forum (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message
            ADD parent_id INT DEFAULT NULL,
            ADD uuid VARCHAR(36) NOT NULL,
            ADD moderation VARCHAR(255) NOT NULL,
            ADD flagged TINYINT(1) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_forum_message SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            UPDATE claro_forum_message SET moderation = "NONE"
        ');
        $this->addSql('
            UPDATE claro_forum_message SET flagged = false
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message
            ADD CONSTRAINT FK_6A49AC0E727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro_forum_message (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_6A49AC0ED17F50A6 ON claro_forum_message (uuid)
        ');
        $this->addSql('
            CREATE INDEX IDX_6A49AC0E727ACA70 ON claro_forum_message (parent_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_forum_user
        ');
        $this->addSql('
            DROP INDEX UNIQ_F2869DFD17F50A6 ON claro_forum
        ');
        $this->addSql('
            ALTER TABLE claro_forum
            ADD activate_notifications TINYINT(1) NOT NULL,
            DROP validationMode,
            DROP maxComment,
            DROP displayMessages,
            DROP dataListOptions,
            DROP lockDate,
            DROP show_overview,
            DROP description,
            DROP uuid
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message
            DROP FOREIGN KEY FK_6A49AC0E727ACA70
        ');
        $this->addSql('
            DROP INDEX UNIQ_6A49AC0ED17F50A6 ON claro_forum_message
        ');
        $this->addSql('
            DROP INDEX IDX_6A49AC0E727ACA70 ON claro_forum_message
        ');
        $this->addSql('
            ALTER TABLE claro_forum_message
            DROP parent_id,
            DROP uuid,
            DROP moderation,
            DROP flagged
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject
            DROP FOREIGN KEY FK_273AA20B29CCBAD0
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject
            DROP FOREIGN KEY FK_273AA20B5BB66C05
        ');
        $this->addSql('
            DROP INDEX UNIQ_273AA20BD17F50A6 ON claro_forum_subject
        ');
        $this->addSql('
            DROP INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject
        ');
        $this->addSql('
            DROP INDEX IDX_273AA20B5BB66C05 ON claro_forum_subject
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject
            ADD category_id INT DEFAULT NULL,
            ADD isSticked TINYINT(1) NOT NULL,
            ADD isClosed TINYINT(1) NOT NULL,
            DROP forum_id,
            DROP poster_id,
            DROP content,
            DROP sticked,
            DROP closed,
            DROP flagged,
            DROP viewCount,
            DROP uuid
        ');
        $this->addSql('
            ALTER TABLE claro_forum_subject
            ADD CONSTRAINT FK_273AA20B12469DE2 FOREIGN KEY (category_id)
            REFERENCES claro_forum_category (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_273AA20B12469DE2 ON claro_forum_subject (category_id)
        ');
    }
}
