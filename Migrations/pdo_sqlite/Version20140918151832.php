<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/18 03:18:57
 */
class Version20140918151832 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_message 
            ADD COLUMN author VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD COLUMN author VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_6A49AC0E23EDC87
        ");
        $this->addSql("
            DROP INDEX IDX_6A49AC0EA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_message AS 
            SELECT id, 
            subject_id, 
            user_id, 
            content, 
            created, 
            updated 
            FROM claro_forum_message
        ");
        $this->addSql("
            DROP TABLE claro_forum_message
        ");
        $this->addSql("
            CREATE TABLE claro_forum_message (
                id INTEGER NOT NULL, 
                subject_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                content CLOB NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6A49AC0E23EDC87 FOREIGN KEY (subject_id) 
                REFERENCES claro_forum_subject (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6A49AC0EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_message (
                id, subject_id, user_id, content, created, 
                updated
            ) 
            SELECT id, 
            subject_id, 
            user_id, 
            content, 
            created, 
            updated 
            FROM __temp__claro_forum_message
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_message
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0E23EDC87 ON claro_forum_message (subject_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0EA76ED395 ON claro_forum_message (user_id)
        ");
        $this->addSql("
            DROP INDEX IDX_273AA20B12469DE2
        ");
        $this->addSql("
            DROP INDEX IDX_273AA20BA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_subject AS 
            SELECT id, 
            category_id, 
            user_id, 
            title, 
            created, 
            updated, 
            isSticked, 
            isClosed 
            FROM claro_forum_subject
        ");
        $this->addSql("
            DROP TABLE claro_forum_subject
        ");
        $this->addSql("
            CREATE TABLE claro_forum_subject (
                id INTEGER NOT NULL, 
                category_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                isSticked BOOLEAN NOT NULL, 
                isClosed BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_273AA20B12469DE2 FOREIGN KEY (category_id) 
                REFERENCES claro_forum_category (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_273AA20BA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_subject (
                id, category_id, user_id, title, created, 
                updated, isSticked, isClosed
            ) 
            SELECT id, 
            category_id, 
            user_id, 
            title, 
            created, 
            updated, 
            isSticked, 
            isClosed 
            FROM __temp__claro_forum_subject
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_subject
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20B12469DE2 ON claro_forum_subject (category_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20BA76ED395 ON claro_forum_subject (user_id)
        ");
    }
}