<?php

namespace Innova\CollecticielBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/10 01:48:47
 */
class Version20150310134844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_comment_read (
                id INTEGER NOT NULL, 
                comment_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_83EB06B9F8697D13 ON innova_collecticielbundle_comment_read (comment_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_83EB06B9A76ED395 ON innova_collecticielbundle_comment_read (user_id)
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_comment (
                id INTEGER NOT NULL, 
                document_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                commentText CLOB DEFAULT NULL, 
                comment_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_A9CB9095C33F7837 ON innova_collecticielbundle_comment (document_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_A9CB9095A76ED395 ON innova_collecticielbundle_comment (user_id)
        ');
        $this->addSql('
            DROP INDEX IDX_1C357F0C1BAD783F
        ');
        $this->addSql('
            DROP INDEX IDX_1C357F0C4D224760
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__innova_collecticielbundle_document AS 
            SELECT id, 
            resource_node_id, 
            type, 
            url, 
            validate 
            FROM innova_collecticielbundle_document
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_document
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_document (
                id INTEGER NOT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                validate BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1C357F0C1BAD783F FOREIGN KEY (resource_node_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ');
        $this->addSql('
            INSERT INTO innova_collecticielbundle_document (
                id, resource_node_id, type, url, validate
            ) 
            SELECT id, 
            resource_node_id, 
            type, 
            url, 
            validate 
            FROM __temp__innova_collecticielbundle_document
        ');
        $this->addSql('
            DROP TABLE __temp__innova_collecticielbundle_document
        ');
        $this->addSql('
            CREATE INDEX IDX_1C357F0C1BAD783F ON innova_collecticielbundle_document (resource_node_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE innova_collecticielbundle_comment_read
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_comment
        ');
        $this->addSql('
            DROP INDEX IDX_1C357F0C1BAD783F
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__innova_collecticielbundle_document AS 
            SELECT id, 
            resource_node_id, 
            type, 
            url, 
            validate 
            FROM innova_collecticielbundle_document
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_document
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_document (
                id INTEGER NOT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                drop_id INTEGER NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                validate BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1C357F0C1BAD783F FOREIGN KEY (resource_node_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1C357F0C4D224760 FOREIGN KEY (drop_id) 
                REFERENCES innova_collecticielbundle_drop (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ');
        $this->addSql('
            INSERT INTO innova_collecticielbundle_document (
                id, resource_node_id, type, url, validate
            ) 
            SELECT id, 
            resource_node_id, 
            type, 
            url, 
            validate 
            FROM __temp__innova_collecticielbundle_document
        ');
        $this->addSql('
            DROP TABLE __temp__innova_collecticielbundle_document
        ');
        $this->addSql('
            CREATE INDEX IDX_1C357F0C1BAD783F ON innova_collecticielbundle_document (resource_node_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document (drop_id)
        ');
    }
}
