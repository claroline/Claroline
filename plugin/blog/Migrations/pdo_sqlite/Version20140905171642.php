<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/09/05 05:16:44
 */
class Version20140905171642 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_post 
            ADD COLUMN viewCounter INTEGER NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD COLUMN display_post_view_counter BOOLEAN DEFAULT '1' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_D1AAC984DAE07E97
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__icap__blog_options AS 
            SELECT id, 
            blog_id, 
            authorize_comment, 
            authorize_anonymous_comment, 
            post_per_page, 
            auto_publish_post, 
            auto_publish_comment, 
            display_title, 
            banner_activate, 
            banner_background_color, 
            banner_height, 
            banner_background_image, 
            banner_background_image_position, 
            banner_background_image_repeat, 
            tag_cloud 
            FROM icap__blog_options
        ');
        $this->addSql('
            DROP TABLE icap__blog_options
        ');
        $this->addSql('
            CREATE TABLE icap__blog_options (
                id INTEGER NOT NULL, 
                blog_id INTEGER DEFAULT NULL, 
                authorize_comment BOOLEAN NOT NULL, 
                authorize_anonymous_comment BOOLEAN NOT NULL, 
                post_per_page INTEGER NOT NULL, 
                auto_publish_post BOOLEAN NOT NULL, 
                auto_publish_comment BOOLEAN NOT NULL, 
                display_title BOOLEAN NOT NULL, 
                banner_activate BOOLEAN NOT NULL, 
                banner_background_color VARCHAR(255) NOT NULL, 
                banner_height INTEGER NOT NULL, 
                banner_background_image VARCHAR(255) DEFAULT NULL, 
                banner_background_image_position VARCHAR(255) NOT NULL, 
                banner_background_image_repeat VARCHAR(255) NOT NULL, 
                tag_cloud INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D1AAC984DAE07E97 FOREIGN KEY (blog_id) 
                REFERENCES icap__blog (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ');
        $this->addSql('
            INSERT INTO icap__blog_options (
                id, blog_id, authorize_comment, authorize_anonymous_comment, 
                post_per_page, auto_publish_post, 
                auto_publish_comment, display_title, 
                banner_activate, banner_background_color, 
                banner_height, banner_background_image, 
                banner_background_image_position, 
                banner_background_image_repeat, 
                tag_cloud
            ) 
            SELECT id, 
            blog_id, 
            authorize_comment, 
            authorize_anonymous_comment, 
            post_per_page, 
            auto_publish_post, 
            auto_publish_comment, 
            display_title, 
            banner_activate, 
            banner_background_color, 
            banner_height, 
            banner_background_image, 
            banner_background_image_position, 
            banner_background_image_repeat, 
            tag_cloud 
            FROM __temp__icap__blog_options
        ');
        $this->addSql('
            DROP TABLE __temp__icap__blog_options
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D1AAC984DAE07E97 ON icap__blog_options (blog_id)
        ');
        $this->addSql('
            DROP INDEX UNIQ_1B067922989D9B62
        ');
        $this->addSql('
            DROP INDEX IDX_1B067922A76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_1B067922DAE07E97
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__icap__blog_post AS 
            SELECT id, 
            user_id, 
            blog_id, 
            title, 
            content, 
            slug, 
            creation_date, 
            modification_date, 
            publication_date, 
            status 
            FROM icap__blog_post
        ');
        $this->addSql('
            DROP TABLE icap__blog_post
        ');
        $this->addSql('
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_1B067922A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1B067922DAE07E97 FOREIGN KEY (blog_id) 
                REFERENCES icap__blog (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ');
        $this->addSql('
            INSERT INTO icap__blog_post (
                id, user_id, blog_id, title, content, 
                slug, creation_date, modification_date, 
                publication_date, status
            ) 
            SELECT id, 
            user_id, 
            blog_id, 
            title, 
            content, 
            slug, 
            creation_date, 
            modification_date, 
            publication_date, 
            status 
            FROM __temp__icap__blog_post
        ');
        $this->addSql('
            DROP TABLE __temp__icap__blog_post
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_1B067922989D9B62 ON icap__blog_post (slug)
        ');
        $this->addSql('
            CREATE INDEX IDX_1B067922A76ED395 ON icap__blog_post (user_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_1B067922DAE07E97 ON icap__blog_post (blog_id)
        ');
    }
}
