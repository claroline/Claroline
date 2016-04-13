<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/05/20 10:15:05
 */
class Version20140520101504 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_comment 
            ADD COLUMN update_date DATETIME DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX IDX_95EB616FA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_95EB616F4B89032C
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__icap__blog_comment AS 
            SELECT id, 
            user_id, 
            post_id, 
            message, 
            creation_date, 
            publication_date, 
            status 
            FROM icap__blog_comment
        ');
        $this->addSql('
            DROP TABLE icap__blog_comment
        ');
        $this->addSql('
            CREATE TABLE icap__blog_comment (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                post_id INTEGER DEFAULT NULL, 
                message CLOB NOT NULL, 
                creation_date DATETIME NOT NULL, 
                publication_date DATETIME DEFAULT NULL, 
                status INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_95EB616FA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_95EB616F4B89032C FOREIGN KEY (post_id) 
                REFERENCES icap__blog_post (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ');
        $this->addSql('
            INSERT INTO icap__blog_comment (
                id, user_id, post_id, message, creation_date, 
                publication_date, status
            ) 
            SELECT id, 
            user_id, 
            post_id, 
            message, 
            creation_date, 
            publication_date, 
            status 
            FROM __temp__icap__blog_comment
        ');
        $this->addSql('
            DROP TABLE __temp__icap__blog_comment
        ');
        $this->addSql('
            CREATE INDEX IDX_95EB616FA76ED395 ON icap__blog_comment (user_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_95EB616F4B89032C ON icap__blog_comment (post_id)
        ');
    }
}
