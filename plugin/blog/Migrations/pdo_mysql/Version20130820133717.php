<?php

namespace Icap\BlogBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/08/20 01:37:19
 */
class Version20130820133717 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__blog_tag (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_8BE678285E237E06 (name),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
                modification_date DATETIME NOT NULL,
                publication_date DATETIME DEFAULT NULL,
                status SMALLINT NOT NULL,
                UNIQUE INDEX UNIQ_1B067922989D9B62 (slug),
                INDEX IDX_1B067922A76ED395 (user_id),
                INDEX IDX_1B067922DAE07E97 (blog_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__blog_post_tag (
                post_id INT NOT NULL,
                tag_id INT NOT NULL,
                INDEX IDX_C3C6F4794B89032C (post_id),
                INDEX IDX_C3C6F479BAD26311 (tag_id),
                PRIMARY KEY(post_id, tag_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__blog_comment (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                post_id INT DEFAULT NULL,
                message LONGTEXT NOT NULL,
                creation_date DATETIME NOT NULL,
                publication_date DATETIME DEFAULT NULL,
                status SMALLINT NOT NULL,
                INDEX IDX_95EB616FA76ED395 (user_id),
                INDEX IDX_95EB616F4B89032C (post_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__blog (
                id INT AUTO_INCREMENT NOT NULL,
                infos LONGTEXT DEFAULT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_FD75E6C4B87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__blog_options (
                id INT AUTO_INCREMENT NOT NULL,
                blog_id INT DEFAULT NULL,
                authorize_comment TINYINT(1) NOT NULL,
                authorize_anonymous_comment TINYINT(1) NOT NULL,
                post_per_page SMALLINT NOT NULL,
                auto_publish_post TINYINT(1) NOT NULL,
                auto_publish_comment TINYINT(1) NOT NULL,
                UNIQUE INDEX UNIQ_D1AAC984DAE07E97 (blog_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
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
            ALTER TABLE icap__blog_options
            ADD CONSTRAINT FK_D1AAC984DAE07E97 FOREIGN KEY (blog_id)
            REFERENCES icap__blog (id)
        ');
    }

    public function down(Schema $schema)
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
            DROP TABLE icap__blog_options
        ');
    }
}
