<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/30 03:34:37
 */
class Version20131030153435 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD COLUMN banner_activate BOOLEAN DEFAULT '1' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD COLUMN banner_background_color VARCHAR(255) DEFAULT 'white' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD COLUMN banner_height INTEGER DEFAULT '100' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD COLUMN banner_image VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD COLUMN banner_image_position INTEGER DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD COLUMN banner_image_repeat INTEGER DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_D1AAC984DAE07E97
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__blog_options AS 
            SELECT id, 
            blog_id, 
            authorize_comment, 
            authorize_anonymous_comment, 
            post_per_page, 
            auto_publish_post, 
            auto_publish_comment 
            FROM icap__blog_options
        ");
        $this->addSql("
            DROP TABLE icap__blog_options
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_D1AAC984DAE07E97 FOREIGN KEY (blog_id) 
                REFERENCES icap__blog (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__blog_options (
                id, blog_id, authorize_comment, authorize_anonymous_comment, 
                post_per_page, auto_publish_post, 
                auto_publish_comment
            ) 
            SELECT id, 
            blog_id, 
            authorize_comment, 
            authorize_anonymous_comment, 
            post_per_page, 
            auto_publish_post, 
            auto_publish_comment 
            FROM __temp__icap__blog_options
        ");
        $this->addSql("
            DROP TABLE __temp__icap__blog_options
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D1AAC984DAE07E97 ON icap__blog_options (blog_id)
        ");
    }
}