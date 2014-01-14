<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/14 12:39:49
 */
class Version20140114123948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_CE19F054B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_path AS 
            SELECT id, 
            path, 
            deployed, 
            modified, 
            resourceNode_id, 
            description 
            FROM innova_path
        ");
        $this->addSql("
            DROP TABLE innova_path
        ");
        $this->addSql("
            CREATE TABLE innova_path (
                id INTEGER NOT NULL, 
                deployed BOOLEAN NOT NULL, 
                modified BOOLEAN NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                structure CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_path (
                id, structure, deployed, modified, 
                resourceNode_id, description
            ) 
            SELECT id, 
            path, 
            deployed, 
            modified, 
            resourceNode_id, 
            description 
            FROM __temp__innova_path
        ");
        $this->addSql("
            DROP TABLE __temp__innova_path
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_CE19F054B87FAB32 ON innova_path (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_CE19F054B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_path AS 
            SELECT id, 
            structure, 
            deployed, 
            modified, 
            description, 
            resourceNode_id 
            FROM innova_path
        ");
        $this->addSql("
            DROP TABLE innova_path
        ");
        $this->addSql("
            CREATE TABLE innova_path (
                id INTEGER NOT NULL, 
                deployed BOOLEAN NOT NULL, 
                modified BOOLEAN NOT NULL, 
                description CLOB DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                path CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_path (
                id, path, deployed, modified, description, 
                resourceNode_id
            ) 
            SELECT id, 
            structure, 
            deployed, 
            modified, 
            description, 
            resourceNode_id 
            FROM __temp__innova_path
        ");
        $this->addSql("
            DROP TABLE __temp__innova_path
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_CE19F054B87FAB32 ON innova_path (resourceNode_id)
        ");
    }
}