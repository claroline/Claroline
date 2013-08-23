<?php

namespace ICAP\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/20 01:37:19
 */
class Version20130820133717 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__blog_tag (
                id INTEGER NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8BE678285E237E06 ON icap__blog_tag (name)
        ");
        $this->addSql("
            CREATE TABLE icap__blog_post (
                id INTEGER NOT NULL,
                user_id INTEGER DEFAULT NULL,
                blog_id INTEGER DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                content CLOB NOT NULL,
                slug VARCHAR(128) NOT NULL,
                creation_date DATETIME NOT NULL,
                modification_date DATETIME NOT NULL,
                publication_date DATETIME DEFAULT NULL,
                status INTEGER NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1B067922989D9B62 ON icap__blog_post (slug)
        ");
        $this->addSql("
            CREATE INDEX IDX_1B067922A76ED395 ON icap__blog_post (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1B067922DAE07E97 ON icap__blog_post (blog_id)
        ");
        $this->addSql("
            CREATE TABLE icap__blog_post_tag (
                post_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                PRIMARY KEY(post_id, tag_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C3C6F4794B89032C ON icap__blog_post_tag (post_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C3C6F479BAD26311 ON icap__blog_post_tag (tag_id)
        ");
        $this->addSql("
            CREATE TABLE icap__blog_comment (
                id INTEGER NOT NULL,
                user_id INTEGER DEFAULT NULL,
                post_id INTEGER DEFAULT NULL,
                message CLOB NOT NULL,
                creation_date DATETIME NOT NULL,
                publication_date DATETIME DEFAULT NULL,
                status INTEGER NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_95EB616FA76ED395 ON icap__blog_comment (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_95EB616F4B89032C ON icap__blog_comment (post_id)
        ");
        $this->addSql("
            CREATE TABLE icap__blog (
                id INTEGER NOT NULL,
                infos CLOB DEFAULT NULL,
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FD75E6C4B87FAB32 ON icap__blog (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE icap__blog_options (
                id INTEGER NOT NULL,
                blog_id INTEGER DEFAULT NULL,
                authorize_comment BOOLEAN NOT NULL,
                authorize_anonymous_comment BOOLEAN NOT NULL,
                post_per_page INTEGER NOT NULL,
                auto_publish_post BOOLEAN NOT NULL,
                auto_publish_comment BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D1AAC984DAE07E97 ON icap__blog_options (blog_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__blog_tag
        ");
        $this->addSql("
            DROP TABLE icap__blog_post
        ");
        $this->addSql("
            DROP TABLE icap__blog_post_tag
        ");
        $this->addSql("
            DROP TABLE icap__blog_comment
        ");
        $this->addSql("
            DROP TABLE icap__blog
        ");
        $this->addSql("
            DROP TABLE icap__blog_options
        ");
    }
}
