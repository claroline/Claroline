<?php

namespace Innova\CollecticielBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/11 09:53:53
 */
class Version20150311095350 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_1C357F0C1BAD783F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_collecticielbundle_document AS 
            SELECT id, 
            resource_node_id, 
            type, 
            url, 
            validate 
            FROM innova_collecticielbundle_document
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_document
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_document (
                id INTEGER NOT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                drop_id INTEGER DEFAULT NULL, 
                type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                validate BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1C357F0C1BAD783F FOREIGN KEY (resource_node_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1C357F0C4D224760 FOREIGN KEY (drop_id) 
                REFERENCES innova_collecticielbundle_drop (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_collecticielbundle_document (
                id, resource_node_id, type, url, validate
            ) 
            SELECT id, 
            resource_node_id, 
            type, 
            url, 
            validate 
            FROM __temp__innova_collecticielbundle_document
        ");
        $this->addSql("
            DROP TABLE __temp__innova_collecticielbundle_document
        ");
        $this->addSql("
            CREATE INDEX IDX_1C357F0C1BAD783F ON innova_collecticielbundle_document (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document (drop_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_1C357F0C1BAD783F
        ");
        $this->addSql("
            DROP INDEX IDX_1C357F0C4D224760
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_collecticielbundle_document AS 
            SELECT id, 
            resource_node_id, 
            type, 
            url, 
            validate 
            FROM innova_collecticielbundle_document
        ");
        $this->addSql("
            DROP TABLE innova_collecticielbundle_document
        ");
        $this->addSql("
            CREATE TABLE innova_collecticielbundle_document (
                id INTEGER NOT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                validate BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1C357F0C1BAD783F FOREIGN KEY (resource_node_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_collecticielbundle_document (
                id, resource_node_id, type, url, validate
            ) 
            SELECT id, 
            resource_node_id, 
            type, 
            url, 
            validate 
            FROM __temp__innova_collecticielbundle_document
        ");
        $this->addSql("
            DROP TABLE __temp__innova_collecticielbundle_document
        ");
        $this->addSql("
            CREATE INDEX IDX_1C357F0C1BAD783F ON innova_collecticielbundle_document (resource_node_id)
        ");
    }
}