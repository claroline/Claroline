<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_sqlsrv;

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
            ALTER TABLE claro_announcement_aggregate 
            ADD resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate ALTER COLUMN id INT IDENTITY NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            DROP CONSTRAINT FK_79BF2C8CBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            ADD CONSTRAINT FK_79BF2C8CB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_79BF2C8CB87FAB32 ON claro_announcement_aggregate (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            DROP COLUMN resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate ALTER COLUMN id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            DROP CONSTRAINT FK_79BF2C8CB87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_79BF2C8CB87FAB32'
            ) 
            ALTER TABLE claro_announcement_aggregate 
            DROP CONSTRAINT UNIQ_79BF2C8CB87FAB32 ELSE 
            DROP INDEX UNIQ_79BF2C8CB87FAB32 ON claro_announcement_aggregate
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            ADD CONSTRAINT FK_79BF2C8CBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
    }
}