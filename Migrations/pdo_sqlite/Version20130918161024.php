<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/18 04:10:24
 */
class Version20130918161024 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_CE19F05482D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_path AS 
            SELECT id, 
            workspace_id, 
            path 
            FROM innova_path
        ");
        $this->addSql("
            DROP TABLE innova_path
        ");
        $this->addSql("
            CREATE TABLE innova_path (
                id INTEGER NOT NULL, 
                path CLOB NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_path (id, resourceNode_id, path) 
            SELECT id, 
            workspace_id, 
            path 
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
            path, 
            resourceNode_id 
            FROM innova_path
        ");
        $this->addSql("
            DROP TABLE innova_path
        ");
        $this->addSql("
            CREATE TABLE innova_path (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                path CLOB NOT NULL, 
                user VARCHAR(255) NOT NULL, 
                edit_date DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CE19F05482D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_path (id, path, workspace_id) 
            SELECT id, 
            path, 
            resourceNode_id 
            FROM __temp__innova_path
        ");
        $this->addSql("
            DROP TABLE __temp__innova_path
        ");
        $this->addSql("
            CREATE INDEX IDX_CE19F05482D40A1F ON innova_path (workspace_id)
        ");
    }
}