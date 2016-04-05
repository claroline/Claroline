<?php

namespace Innova\CollecticielBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/10 01:52:19
 */
class Version20150310135217 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_A9CB9095C33F7837
        ");
        $this->addSql("
            DROP INDEX IDX_A9CB9095A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_collecticielbundle_comment AS 
            SELECT id, 
            user_id, 
            document_id, 
            commentText, 
            comment_date 
            FROM innova_collecticielbundle_comment
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_comment
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_comment (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                document_id INTEGER NOT NULL, 
                comment_date DATETIME NOT NULL, 
                comment_text CLOB DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A9CB9095A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A9CB9095C33F7837 FOREIGN KEY (document_id) 
                REFERENCES innova_collecticielbundle_document (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_collecticielbundle_comment (
                id, user_id, document_id, comment_text, 
                comment_date
            ) 
            SELECT id, 
            user_id, 
            document_id, 
            commentText, 
            comment_date 
            FROM __temp__innova_collecticielbundle_comment
        ");
        $this->addSql("
            DROP TABLE __temp__innova_collecticielbundle_comment
        ");
        $this->addSql("
            CREATE INDEX IDX_A9CB9095C33F7837 ON innova_collecticielbundle_comment (document_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A9CB9095A76ED395 ON innova_collecticielbundle_comment (user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_A9CB9095C33F7837
        ");
        $this->addSql("
            DROP INDEX IDX_A9CB9095A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_collecticielbundle_comment AS 
            SELECT id, 
            document_id, 
            user_id, 
            comment_text, 
            comment_date 
            FROM innova_collecticielbundle_comment
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_comment
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_comment (
                id INTEGER NOT NULL, 
                document_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                comment_date DATETIME NOT NULL, 
                commentText CLOB DEFAULT NULL COLLATE utf8_unicode_ci, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A9CB9095C33F7837 FOREIGN KEY (document_id) 
                REFERENCES innova_collecticielbundle_document (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A9CB9095A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_collecticielbundle_comment (
                id, document_id, user_id, commentText, 
                comment_date
            ) 
            SELECT id, 
            document_id, 
            user_id, 
            comment_text, 
            comment_date 
            FROM __temp__innova_collecticielbundle_comment
        ");
        $this->addSql("
            DROP TABLE __temp__innova_collecticielbundle_comment
        ");
        $this->addSql("
            CREATE INDEX IDX_A9CB9095C33F7837 ON innova_collecticielbundle_comment (document_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A9CB9095A76ED395 ON innova_collecticielbundle_comment (user_id)
        ");
    }
}