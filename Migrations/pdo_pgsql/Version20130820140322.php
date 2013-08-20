<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_pgsql;

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
            ADD resourceNode_id INT DEFAULT NULL
        ");
        $this->addSql("
            CREATE SEQUENCE claro_announcement_aggregate_id_seq
        ");
        $this->addSql("
            SELECT setval(
                'claro_announcement_aggregate_id_seq', 
                (
                    SELECT MAX(id) 
                    FROM claro_announcement_aggregate
                )
            )
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate ALTER id 
            SET 
                DEFAULT nextval(
                    'claro_announcement_aggregate_id_seq'
                )
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            DROP CONSTRAINT FK_79BF2C8CBF396750
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            ADD CONSTRAINT FK_79BF2C8CB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_79BF2C8CB87FAB32 ON claro_announcement_aggregate (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            DROP resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate ALTER id 
            DROP DEFAULT
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            DROP CONSTRAINT FK_79BF2C8CB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_79BF2C8CB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            ADD CONSTRAINT FK_79BF2C8CBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }
}