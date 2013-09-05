<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/09 09:17:59
 */
class Version20130809091758 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_forum (
                id INT IDENTITY NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F2869DFB87FAB32 ON claro_forum (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_forum_message (
                id INT IDENTITY NOT NULL, 
                subject_id INT, 
                user_id INT, 
                content NVARCHAR(255) NOT NULL, 
                created DATETIME2(6) NOT NULL, 
                updated DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0E23EDC87 ON claro_forum_message (subject_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0EA76ED395 ON claro_forum_message (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_forum_options (
                id INT IDENTITY NOT NULL, 
                subjects INT NOT NULL, 
                messages INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_forum_subject (
                id INT IDENTITY NOT NULL, 
                forum_id INT, 
                user_id INT, 
                title NVARCHAR(255) NOT NULL, 
                created DATETIME2(6) NOT NULL, 
                updated DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject (forum_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20BA76ED395 ON claro_forum_subject (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum 
            ADD CONSTRAINT FK_F2869DFB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0E23EDC87 FOREIGN KEY (subject_id) 
            REFERENCES claro_forum_subject (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP CONSTRAINT FK_273AA20B29CCBAD0
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            DROP CONSTRAINT FK_6A49AC0E23EDC87
        ");
        $this->addSql("
            DROP TABLE claro_forum
        ");
        $this->addSql("
            DROP TABLE claro_forum_message
        ");
        $this->addSql("
            DROP TABLE claro_forum_options
        ");
        $this->addSql("
            DROP TABLE claro_forum_subject
        ");
    }
}