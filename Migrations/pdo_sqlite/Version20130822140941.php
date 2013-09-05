<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/22 02:09:42
 */
class Version20130822140941 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_F2869DFB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum AS 
            SELECT id, 
            resourceNode_id 
            FROM claro_forum
        ");
        $this->addSql("
            DROP TABLE claro_forum
        ");
        $this->addSql("
            CREATE TABLE claro_forum (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F2869DFB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum (id, resourceNode_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_forum
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F2869DFB87FAB32 ON claro_forum (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_F2869DFB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum AS 
            SELECT id, 
            resourceNode_id 
            FROM claro_forum
        ");
        $this->addSql("
            DROP TABLE claro_forum
        ");
        $this->addSql("
            CREATE TABLE claro_forum (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F2869DFB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum (id, resourceNode_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_forum
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F2869DFB87FAB32 ON claro_forum (resourceNode_id)
        ");
    }
}