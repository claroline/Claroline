<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/06 11:57:39
 */
class Version20130906115738 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_forum (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F2869DFB87FAB32 ON claro_forum (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE claro_forum_message (
                id INTEGER NOT NULL, 
                subject_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                content CLOB NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                PRIMARY KEY(id)
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
                id INTEGER NOT NULL, 
                subjects INTEGER NOT NULL, 
                messages INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_forum_subject (
                id INTEGER NOT NULL, 
                forum_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject (forum_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20BA76ED395 ON claro_forum_subject (user_id)
        ");
    }

    public function down(Schema $schema)
    {
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