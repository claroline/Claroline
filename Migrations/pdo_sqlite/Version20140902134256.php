<?php

namespace Icap\WebsiteBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/02 01:42:57
 */
class Version20140902134256 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_452309F8B87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_452309F879066886
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__website AS 
            SELECT id, 
            root_id, 
            resourceNode_id 
            FROM icap__website
        ");
        $this->addSql("
            DROP TABLE icap__website
        ");
        $this->addSql("
            CREATE TABLE icap__website (
                id INTEGER NOT NULL, 
                root_id INTEGER DEFAULT NULL, 
                options_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_452309F879066886 FOREIGN KEY (root_id) 
                REFERENCES icap__website_page (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_452309F8B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_452309F83ADB05F1 FOREIGN KEY (options_id) 
                REFERENCES icap__website_options (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__website (id, root_id, resourceNode_id) 
            SELECT id, 
            root_id, 
            resourceNode_id 
            FROM __temp__icap__website
        ");
        $this->addSql("
            DROP TABLE __temp__icap__website
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F8B87FAB32 ON icap__website (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F879066886 ON icap__website (root_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F83ADB05F1 ON icap__website (options_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_452309F879066886
        ");
        $this->addSql("
            DROP INDEX UNIQ_452309F83ADB05F1
        ");
        $this->addSql("
            DROP INDEX UNIQ_452309F8B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__website AS 
            SELECT id, 
            root_id, 
            resourceNode_id 
            FROM icap__website
        ");
        $this->addSql("
            DROP TABLE icap__website
        ");
        $this->addSql("
            CREATE TABLE icap__website (
                id INTEGER NOT NULL, 
                root_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_452309F879066886 FOREIGN KEY (root_id) 
                REFERENCES icap__website_page (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_452309F8B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__website (id, root_id, resourceNode_id) 
            SELECT id, 
            root_id, 
            resourceNode_id 
            FROM __temp__icap__website
        ");
        $this->addSql("
            DROP TABLE __temp__icap__website
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F879066886 ON icap__website (root_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F8B87FAB32 ON icap__website (resourceNode_id)
        ");
    }
}