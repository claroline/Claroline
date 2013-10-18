<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/17 04:23:19
 */
class Version20131017162318 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_305E9E56B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_305E9E568CF60863
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_nonDigitalResource AS 
            SELECT id, 
            description, 
            resourceNode_id, 
            nonDigitalResourceType_id 
            FROM innova_nonDigitalResource
        ");
        $this->addSql("
            DROP TABLE innova_nonDigitalResource
        ");
        $this->addSql("
            CREATE TABLE innova_nonDigitalResource (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                nonDigitalResourceType_id INTEGER DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_305E9E568CF60863 FOREIGN KEY (nonDigitalResourceType_id) 
                REFERENCES innova_nonDigitalResourceType (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_305E9E56B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_nonDigitalResource (
                id, description, resourceNode_id, 
                nonDigitalResourceType_id
            ) 
            SELECT id, 
            description, 
            resourceNode_id, 
            nonDigitalResourceType_id 
            FROM __temp__innova_nonDigitalResource
        ");
        $this->addSql("
            DROP TABLE __temp__innova_nonDigitalResource
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_305E9E56B87FAB32 ON innova_nonDigitalResource (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_305E9E568CF60863 ON innova_nonDigitalResource (nonDigitalResourceType_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_305E9E56B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_305E9E568CF60863
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_nonDigitalResource AS 
            SELECT id, 
            description, 
            resourceNode_id, 
            nonDigitalResourceType_id 
            FROM innova_nonDigitalResource
        ");
        $this->addSql("
            DROP TABLE innova_nonDigitalResource
        ");
        $this->addSql("
            CREATE TABLE innova_nonDigitalResource (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                nonDigitalResourceType_id INTEGER DEFAULT NULL, 
                description CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_305E9E56B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_305E9E568CF60863 FOREIGN KEY (nonDigitalResourceType_id) 
                REFERENCES innova_nonDigitalResourceType (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_nonDigitalResource (
                id, description, resourceNode_id, 
                nonDigitalResourceType_id
            ) 
            SELECT id, 
            description, 
            resourceNode_id, 
            nonDigitalResourceType_id 
            FROM __temp__innova_nonDigitalResource
        ");
        $this->addSql("
            DROP TABLE __temp__innova_nonDigitalResource
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_305E9E56B87FAB32 ON innova_nonDigitalResource (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_305E9E568CF60863 ON innova_nonDigitalResource (nonDigitalResourceType_id)
        ");
    }
}