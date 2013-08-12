<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/09 02:52:40
 */
class Version20130809145239 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_shortcut AS 
            SELECT id, 
            resourceNode_id 
            FROM claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE claro_resource_shortcut
        ");
        $this->addSql("
            CREATE TABLE claro_resource_shortcut (
                id INTEGER NOT NULL, 
                target_id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_shortcut (id, resourceNode_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut (target_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8158E0B66
        ");
        $this->addSql("
            DROP INDEX UNIQ_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_shortcut AS 
            SELECT id, 
            resourceNode_id 
            FROM claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE claro_resource_shortcut
        ");
        $this->addSql("
            CREATE TABLE claro_resource_shortcut (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_shortcut (id, resourceNode_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
    }
}