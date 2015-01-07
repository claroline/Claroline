<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/06 04:37:10
 */
class Version20150106163709 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_FD75E6C4B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__blog AS 
            SELECT id, 
            infos, 
            resourceNode_id 
            FROM icap__blog
        ");
        $this->addSql("
            DROP TABLE icap__blog
        ");
        $this->addSql("
            CREATE TABLE icap__blog (
                id INTEGER NOT NULL, 
                infos CLOB DEFAULT NULL COLLATE utf8_unicode_ci, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_FD75E6C4B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__blog (id, infos, resourceNode_id) 
            SELECT id, 
            infos, 
            resourceNode_id 
            FROM __temp__icap__blog
        ");
        $this->addSql("
            DROP TABLE __temp__icap__blog
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FD75E6C4B87FAB32 ON icap__blog (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FD75E6C4B87FAB32 ON icap__blog (resourceNode_id)
        ");
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
            auto_publish_comment, 
            display_title, 
            banner_activate, 
            banner_background_color, 
            banner_height, 
            banner_background_image, 
            banner_background_image_position, 
            banner_background_image_repeat, 
            tag_cloud, 
            display_post_view_counter 
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
                post_per_page SMALLINT NOT NULL, 
                auto_publish_post BOOLEAN NOT NULL, 
                auto_publish_comment BOOLEAN NOT NULL, 
                display_title BOOLEAN NOT NULL, 
                banner_activate BOOLEAN NOT NULL, 
                banner_background_color VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                banner_height SMALLINT NOT NULL, 
                banner_background_image VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                banner_background_image_position VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                banner_background_image_repeat VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                tag_cloud SMALLINT DEFAULT NULL, 
                display_post_view_counter BOOLEAN DEFAULT '1' NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D1AAC984DAE07E97 FOREIGN KEY (blog_id) 
                REFERENCES icap__blog (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__blog_options (
                id, blog_id, authorize_comment, authorize_anonymous_comment, 
                post_per_page, auto_publish_post, 
                auto_publish_comment, display_title, 
                banner_activate, banner_background_color, 
                banner_height, banner_background_image, 
                banner_background_image_position, 
                banner_background_image_repeat, 
                tag_cloud, display_post_view_counter
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
            tag_cloud, 
            display_post_view_counter 
            FROM __temp__icap__blog_options
        ");
        $this->addSql("
            DROP TABLE __temp__icap__blog_options
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D1AAC984DAE07E97 ON icap__blog_options (blog_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D1AAC984DAE07E97 ON icap__blog_options (blog_id)
        ");
        $this->addSql("
            DROP INDEX IDX_294D4E02AB7B5A55
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__blog_widget_list_blog AS 
            SELECT id, 
            widgetInstance_id, 
            blog_id 
            FROM icap__blog_widget_list_blog
        ");
        $this->addSql("
            DROP TABLE icap__blog_widget_list_blog
        ");
        $this->addSql("
            CREATE TABLE icap__blog_widget_list_blog (
                id INTEGER NOT NULL, 
                widgetInstance_id INTEGER NOT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_294D4E02AB7B5A55 FOREIGN KEY (widgetInstance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_294D4E02B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__blog_widget_list_blog (
                id, widgetInstance_id, resourceNode_id
            ) 
            SELECT id, 
            widgetInstance_id, 
            blog_id 
            FROM __temp__icap__blog_widget_list_blog
        ");
        $this->addSql("
            DROP TABLE __temp__icap__blog_widget_list_blog
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02AB7B5A55 ON icap__blog_widget_list_blog (widgetInstance_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02B87FAB32 ON icap__blog_widget_list_blog (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_FD75E6C4B87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_FD75E6C4B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__blog AS 
            SELECT id, 
            infos, 
            resourceNode_id 
            FROM icap__blog
        ");
        $this->addSql("
            DROP TABLE icap__blog
        ");
        $this->addSql("
            CREATE TABLE icap__blog (
                id INTEGER NOT NULL, 
                infos CLOB DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_FD75E6C4B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__blog (id, infos, resourceNode_id) 
            SELECT id, 
            infos, 
            resourceNode_id 
            FROM __temp__icap__blog
        ");
        $this->addSql("
            DROP TABLE __temp__icap__blog
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FD75E6C4B87FAB32 ON icap__blog (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX IDX_D1AAC984DAE07E97
        ");
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
            auto_publish_comment, 
            display_title, 
            banner_activate, 
            display_post_view_counter, 
            banner_background_color, 
            banner_height, 
            banner_background_image, 
            banner_background_image_position, 
            banner_background_image_repeat, 
            tag_cloud 
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
                post_per_page SMALLINT NOT NULL, 
                auto_publish_post BOOLEAN NOT NULL, 
                auto_publish_comment BOOLEAN NOT NULL, 
                display_title BOOLEAN NOT NULL, 
                banner_activate BOOLEAN NOT NULL, 
                display_post_view_counter BOOLEAN DEFAULT '1' NOT NULL, 
                banner_background_color VARCHAR(255) NOT NULL, 
                banner_height SMALLINT NOT NULL, 
                banner_background_image VARCHAR(255) DEFAULT NULL, 
                banner_background_image_position VARCHAR(255) NOT NULL, 
                banner_background_image_repeat VARCHAR(255) NOT NULL, 
                tag_cloud SMALLINT DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D1AAC984DAE07E97 FOREIGN KEY (blog_id) 
                REFERENCES icap__blog (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__blog_options (
                id, blog_id, authorize_comment, authorize_anonymous_comment, 
                post_per_page, auto_publish_post, 
                auto_publish_comment, display_title, 
                banner_activate, display_post_view_counter, 
                banner_background_color, banner_height, 
                banner_background_image, banner_background_image_position, 
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
            display_post_view_counter, 
            banner_background_color, 
            banner_height, 
            banner_background_image, 
            banner_background_image_position, 
            banner_background_image_repeat, 
            tag_cloud 
            FROM __temp__icap__blog_options
        ");
        $this->addSql("
            DROP TABLE __temp__icap__blog_options
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D1AAC984DAE07E97 ON icap__blog_options (blog_id)
        ");
        $this->addSql("
            DROP INDEX IDX_294D4E02B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_294D4E02AB7B5A55
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__blog_widget_list_blog AS 
            SELECT id, 
            resourceNode_id, 
            widgetInstance_id 
            FROM icap__blog_widget_list_blog
        ");
        $this->addSql("
            DROP TABLE icap__blog_widget_list_blog
        ");
        $this->addSql("
            CREATE TABLE icap__blog_widget_list_blog (
                id INTEGER NOT NULL, 
                widgetInstance_id INTEGER NOT NULL, 
                blog_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_294D4E02AB7B5A55 FOREIGN KEY (widgetInstance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__blog_widget_list_blog (id, blog_id, widgetInstance_id) 
            SELECT id, 
            resourceNode_id, 
            widgetInstance_id 
            FROM __temp__icap__blog_widget_list_blog
        ");
        $this->addSql("
            DROP TABLE __temp__icap__blog_widget_list_blog
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02AB7B5A55 ON icap__blog_widget_list_blog (widgetInstance_id)
        ");
    }
}