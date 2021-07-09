<?php

namespace Icap\BlogBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:34:31
 */
class Version20180313092037 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE icap__blog_tag (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                slug VARCHAR(128) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_8BE678285E237E06 (name), 
                UNIQUE INDEX UNIQ_8BE67828989D9B62 (slug), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__blog_post (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                blog_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                content LONGTEXT NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME DEFAULT NULL, 
                publication_date DATETIME DEFAULT NULL, 
                viewCounter INT DEFAULT 0 NOT NULL, 
                pinned TINYINT(1) NOT NULL, 
                status SMALLINT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_1B067922989D9B62 (slug), 
                UNIQUE INDEX UNIQ_1B067922D17F50A6 (uuid), 
                INDEX IDX_1B067922A76ED395 (user_id), 
                INDEX IDX_1B067922DAE07E97 (blog_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__blog_post_tag (
                post_id INT NOT NULL, 
                tag_id INT NOT NULL, 
                INDEX IDX_C3C6F4794B89032C (post_id), 
                INDEX IDX_C3C6F479BAD26311 (tag_id), 
                PRIMARY KEY(post_id, tag_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__blog_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                post_id INT DEFAULT NULL, 
                message LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                publication_date DATETIME DEFAULT NULL, 
                update_date DATETIME DEFAULT NULL, 
                reported SMALLINT NOT NULL, 
                status SMALLINT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_95EB616FD17F50A6 (uuid), 
                INDEX IDX_95EB616FA76ED395 (user_id), 
                INDEX IDX_95EB616F4B89032C (post_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__blog (
                id INT AUTO_INCREMENT NOT NULL, 
                infos LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_FD75E6C4D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_FD75E6C4B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__blog_member (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                blog_id INT DEFAULT NULL, 
                trusted TINYINT(1) NOT NULL, 
                banned TINYINT(1) NOT NULL, 
                INDEX IDX_34A6FF39A76ED395 (user_id), 
                INDEX IDX_34A6FF39DAE07E97 (blog_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE icap__blog_options (
                id INT AUTO_INCREMENT NOT NULL, 
                blog_id INT DEFAULT NULL, 
                authorize_comment TINYINT(1) NOT NULL, 
                authorize_anonymous_comment TINYINT(1) NOT NULL, 
                post_per_page SMALLINT NOT NULL, 
                auto_publish_post TINYINT(1) NOT NULL, 
                auto_publish_comment TINYINT(1) NOT NULL, 
                comment_moderation_mode SMALLINT NOT NULL, 
                display_title TINYINT(1) NOT NULL, 
                display_full_posts TINYINT(1) NOT NULL, 
                banner_activate TINYINT(1) NOT NULL, 
                display_post_view_counter TINYINT(1) DEFAULT '1' NOT NULL, 
                banner_background_color VARCHAR(255) NOT NULL, 
                banner_height SMALLINT NOT NULL, 
                banner_background_image VARCHAR(255) DEFAULT NULL, 
                banner_background_image_position VARCHAR(255) NOT NULL, 
                banner_background_image_repeat VARCHAR(255) NOT NULL, 
                tag_cloud SMALLINT DEFAULT NULL, 
                display_list_widget_blog_right VARCHAR(255) DEFAULT '01112131415161' NOT NULL, 
                tag_top_mode TINYINT(1) NOT NULL, 
                max_tag SMALLINT DEFAULT 50 NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_D1AAC984D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_D1AAC984DAE07E97 (blog_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE icap__blog_post 
            ADD CONSTRAINT FK_1B067922A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            ADD CONSTRAINT FK_1B067922DAE07E97 FOREIGN KEY (blog_id) 
            REFERENCES icap__blog (id)
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post_tag 
            ADD CONSTRAINT FK_C3C6F4794B89032C FOREIGN KEY (post_id) 
            REFERENCES icap__blog_post (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post_tag 
            ADD CONSTRAINT FK_C3C6F479BAD26311 FOREIGN KEY (tag_id) 
            REFERENCES icap__blog_tag (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__blog_comment 
            ADD CONSTRAINT FK_95EB616FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE icap__blog_comment 
            ADD CONSTRAINT FK_95EB616F4B89032C FOREIGN KEY (post_id) 
            REFERENCES icap__blog_post (id)
        ');
        $this->addSql('
            ALTER TABLE icap__blog 
            ADD CONSTRAINT FK_FD75E6C4B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__blog_member 
            ADD CONSTRAINT FK_34A6FF39A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE icap__blog_member 
            ADD CONSTRAINT FK_34A6FF39DAE07E97 FOREIGN KEY (blog_id) 
            REFERENCES icap__blog (id)
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT FK_D1AAC984DAE07E97 FOREIGN KEY (blog_id) 
            REFERENCES icap__blog (id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__blog_post_tag 
            DROP FOREIGN KEY FK_C3C6F479BAD26311
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post_tag 
            DROP FOREIGN KEY FK_C3C6F4794B89032C
        ');
        $this->addSql('
            ALTER TABLE icap__blog_comment 
            DROP FOREIGN KEY FK_95EB616F4B89032C
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            DROP FOREIGN KEY FK_1B067922DAE07E97
        ');
        $this->addSql('
            ALTER TABLE icap__blog_member 
            DROP FOREIGN KEY FK_34A6FF39DAE07E97
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP FOREIGN KEY FK_D1AAC984DAE07E97
        ');
        $this->addSql('
            DROP TABLE icap__blog_tag
        ');
        $this->addSql('
            DROP TABLE icap__blog_post
        ');
        $this->addSql('
            DROP TABLE icap__blog_post_tag
        ');
        $this->addSql('
            DROP TABLE icap__blog_comment
        ');
        $this->addSql('
            DROP TABLE icap__blog
        ');
        $this->addSql('
            DROP TABLE icap__blog_member
        ');
        $this->addSql('
            DROP TABLE icap__blog_options
        ');
    }
}
