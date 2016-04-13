<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/09/08 03:47:50
 */
class Version20140908154749 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
            status, 
            viewCounter 
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
                publication_date DATETIME DEFAULT NULL, 
                status INTEGER NOT NULL, 
                viewCounter INTEGER DEFAULT 0 NOT NULL, 
                modification_date DATETIME DEFAULT NULL, 
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
                publication_date, status, viewCounter
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
            status, 
            viewCounter 
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

    public function down(Schema $schema)
    {
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
            viewCounter, 
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
                publication_date DATETIME DEFAULT NULL, 
                viewCounter INTEGER NOT NULL, 
                status INTEGER NOT NULL, 
                modification_date DATETIME NOT NULL, 
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
                publication_date, viewCounter, status
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
            viewCounter, 
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
