<?php

namespace Icap\WikiBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/31 08:59:46
 */
class Version20131031085945 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki 
            ADD COLUMN mode INTEGER DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_1FAD6B8179066886
        ");
        $this->addSql("
            DROP INDEX UNIQ_1FAD6B81B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__wiki AS 
            SELECT id, 
            root_id, 
            resourceNode_id 
            FROM icap__wiki
        ");
        $this->addSql("
            DROP TABLE icap__wiki
        ");
        $this->addSql("
            CREATE TABLE icap__wiki (
                id INTEGER NOT NULL, 
                root_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1FAD6B8179066886 FOREIGN KEY (root_id) 
                REFERENCES icap__wiki_section (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1FAD6B81B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__wiki (id, root_id, resourceNode_id) 
            SELECT id, 
            root_id, 
            resourceNode_id 
            FROM __temp__icap__wiki
        ");
        $this->addSql("
            DROP TABLE __temp__icap__wiki
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1FAD6B8179066886 ON icap__wiki (root_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1FAD6B81B87FAB32 ON icap__wiki (resourceNode_id)
        ");
    }
}