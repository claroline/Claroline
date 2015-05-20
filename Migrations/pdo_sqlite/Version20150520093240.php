<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/05/20 09:32:41
 */
class Version20150520093240 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_CE19F054B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_path AS 
            SELECT id, 
            structure, 
            published, 
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
                structure CLOB NOT NULL COLLATE utf8_unicode_ci, 
                modified BOOLEAN NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                description CLOB DEFAULT NULL COLLATE utf8_unicode_ci, 
                breadcrumbs BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_path (
                id, structure, breadcrumbs, modified, 
                resourceNode_id, description
            ) 
            SELECT id, 
            structure, 
            published, 
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
            breadcrumbs, 
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
                structure CLOB NOT NULL, 
                modified BOOLEAN NOT NULL, 
                description CLOB DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                published BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_path (
                id, structure, published, modified, 
                description, resourceNode_id
            ) 
            SELECT id, 
            structure, 
            breadcrumbs, 
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