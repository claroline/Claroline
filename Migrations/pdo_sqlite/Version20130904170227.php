<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/04 05:02:29
 */
class Version20130904170227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX uniq_f2869dfb87fab32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum AS 
            SELECT id, 
            resourcenode_id 
            FROM claro_forum
        ");
        $this->addSql("
            DROP TABLE claro_forum
        ");
        $this->addSql("
            CREATE TABLE claro_forum (
                id INTEGER NOT NULL, 
                resourcenode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT fk_f2869dfb87fab32 FOREIGN KEY (resourcenode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum (id, resourcenode_id) 
            SELECT id, 
            resourcenode_id 
            FROM __temp__claro_forum
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum
        ");
        $this->addSql("
            CREATE UNIQUE INDEX uniq_f2869dfb87fab32 ON claro_forum (resourcenode_id)
        ");
        $this->addSql("
            DROP INDEX idx_6a49ac0ea76ed395
        ");
        $this->addSql("
            DROP INDEX idx_6a49ac0e23edc87
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_message AS 
            SELECT id, 
            user_id, 
            subject_id, 
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
                user_id INTEGER DEFAULT NULL, 
                subject_id INTEGER DEFAULT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT fk_6a49ac0ea76ed395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT fk_6a49ac0e23edc87 FOREIGN KEY (subject_id) 
                REFERENCES claro_forum_subject (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_message (
                id, user_id, subject_id, content, created, 
                updated
            ) 
            SELECT id, 
            user_id, 
            subject_id, 
            content, 
            created, 
            updated 
            FROM __temp__claro_forum_message
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_message
        ");
        $this->addSql("
            CREATE INDEX idx_6a49ac0ea76ed395 ON claro_forum_message (user_id)
        ");
        $this->addSql("
            CREATE INDEX idx_6a49ac0e23edc87 ON claro_forum_message (subject_id)
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_options AS 
            SELECT id, 
            subjects, 
            messages 
            FROM claro_forum_options
        ");
        $this->addSql("
            DROP TABLE claro_forum_options
        ");
        $this->addSql("
            CREATE TABLE claro_forum_options (
                id INTEGER NOT NULL, 
                subjects INTEGER NOT NULL, 
                messages INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_options (id, subjects, messages) 
            SELECT id, 
            subjects, 
            messages 
            FROM __temp__claro_forum_options
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_options
        ");
        $this->addSql("
            DROP INDEX idx_273aa20b29ccbad0
        ");
        $this->addSql("
            DROP INDEX idx_273aa20ba76ed395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_subject AS 
            SELECT id, 
            user_id, 
            forum_id, 
            title, 
            created, 
            updated 
            FROM claro_forum_subject
        ");
        $this->addSql("
            DROP TABLE claro_forum_subject
        ");
        $this->addSql("
            CREATE TABLE claro_forum_subject (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                forum_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT fk_273aa20ba76ed395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT fk_273aa20b29ccbad0 FOREIGN KEY (forum_id) 
                REFERENCES claro_forum (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_subject (
                id, user_id, forum_id, title, created, 
                updated
            ) 
            SELECT id, 
            user_id, 
            forum_id, 
            title, 
            created, 
            updated 
            FROM __temp__claro_forum_subject
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_subject
        ");
        $this->addSql("
            CREATE INDEX idx_273aa20b29ccbad0 ON claro_forum_subject (forum_id)
        ");
        $this->addSql("
            CREATE INDEX idx_273aa20ba76ed395 ON claro_forum_subject (user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_options AS 
            SELECT id, 
            subjects, 
            messages 
            FROM claro_forum_options
        ");
        $this->addSql("
            DROP TABLE claro_forum_options
        ");
        $this->addSql("
            CREATE TABLE claro_forum_options (
                id INTEGER NOT NULL, 
                subjects INTEGER NOT NULL, 
                messages INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_options (id, subjects, messages) 
            SELECT id, 
            subjects, 
            messages 
            FROM __temp__claro_forum_options
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_options
        ");
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
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                content VARCHAR(255) NOT NULL, 
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
            DROP INDEX IDX_273AA20B29CCBAD0
        ");
        $this->addSql("
            DROP INDEX IDX_273AA20BA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_subject AS 
            SELECT id, 
            forum_id, 
            user_id, 
            title, 
            created, 
            updated 
            FROM claro_forum_subject
        ");
        $this->addSql("
            DROP TABLE claro_forum_subject
        ");
        $this->addSql("
            CREATE TABLE claro_forum_subject (
                id INTEGER NOT NULL, 
                forum_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id) 
                REFERENCES claro_forum (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_273AA20BA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_subject (
                id, forum_id, user_id, title, created, 
                updated
            ) 
            SELECT id, 
            forum_id, 
            user_id, 
            title, 
            created, 
            updated 
            FROM __temp__claro_forum_subject
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_subject
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject (forum_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20BA76ED395 ON claro_forum_subject (user_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_F2869DFB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum AS 
            SELECT id, 
            resourceNode_id 
            FROM claro_forum
        ");
        $this->addSql("
            DROP TABLE claro_forum
        ");
        $this->addSql("
            CREATE TABLE claro_forum (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F2869DFB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum (id, resourceNode_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_forum
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F2869DFB87FAB32 ON claro_forum (resourceNode_id)
        ");
    }
}