<?php

namespace Icap\SocialmediaBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/27 05:00:12
 */
class Version20150427170011 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__socialmedia_share (
                id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                network VARCHAR(255) DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_4DB117C589329D25 ON icap__socialmedia_share (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_4DB117C5A76ED395 ON icap__socialmedia_share (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__socialmedia_like (
                id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7C98AD9089329D25 ON icap__socialmedia_like (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7C98AD90A76ED395 ON icap__socialmedia_like (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__socialmedia_comment (
                id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                text CLOB DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6FC00C3089329D25 ON icap__socialmedia_comment (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6FC00C30A76ED395 ON icap__socialmedia_comment (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__socialmedia_wall_item (
                id INTEGER NOT NULL, 
                like_id INTEGER DEFAULT NULL, 
                share_id INTEGER DEFAULT NULL, 
                comment_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_436BC420859BFA32 ON icap__socialmedia_wall_item (like_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_436BC4202AE63FDB ON icap__socialmedia_wall_item (share_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_436BC420F8697D13 ON icap__socialmedia_wall_item (comment_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_436BC420A76ED395 ON icap__socialmedia_wall_item (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__socialmedia_note (
                id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                text CLOB DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1F46173789329D25 ON icap__socialmedia_note (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1F461737A76ED395 ON icap__socialmedia_note (user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__socialmedia_share
        ");
        $this->addSql("
            DROP TABLE icap__socialmedia_like
        ");
        $this->addSql("
            DROP TABLE icap__socialmedia_comment
        ");
        $this->addSql("
            DROP TABLE icap__socialmedia_wall_item
        ");
        $this->addSql("
            DROP TABLE icap__socialmedia_note
        ");
    }
}