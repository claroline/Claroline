<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/20 02:03:25
 */
class Version20130820140322 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_announcement_aggregate AS 
            SELECT id 
            FROM claro_announcement_aggregate
        ");
        $this->addSql("
            DROP TABLE claro_announcement_aggregate
        ");
        $this->addSql("
            CREATE TABLE claro_announcement_aggregate (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_79BF2C8CB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_announcement_aggregate (id) 
            SELECT id 
            FROM __temp__claro_announcement_aggregate
        ");
        $this->addSql("
            DROP TABLE __temp__claro_announcement_aggregate
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_79BF2C8CB87FAB32 ON claro_announcement_aggregate (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_79BF2C8CB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_announcement_aggregate AS 
            SELECT id 
            FROM claro_announcement_aggregate
        ");
        $this->addSql("
            DROP TABLE claro_announcement_aggregate
        ");
        $this->addSql("
            CREATE TABLE claro_announcement_aggregate (
                id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_79BF2C8CBF396750 FOREIGN KEY (id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_announcement_aggregate (id) 
            SELECT id 
            FROM __temp__claro_announcement_aggregate
        ");
        $this->addSql("
            DROP TABLE __temp__claro_announcement_aggregate
        ");
    }
}