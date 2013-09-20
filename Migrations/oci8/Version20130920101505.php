<?php

namespace Innova\PathBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/20 10:15:05
 */
class Version20130920101505 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step2resource RENAME COLUMN resourcenode_id TO resource_id
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            DROP CONSTRAINT FK_447C5959B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_447C5959B87FAB32
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            ADD CONSTRAINT FK_447C595989329D25 FOREIGN KEY (resource_id) 
            REFERENCES innova_resource (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_447C595989329D25 ON innova_step2resource (resource_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step2resource RENAME COLUMN resource_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            DROP CONSTRAINT FK_447C595989329D25
        ");
        $this->addSql("
            DROP INDEX IDX_447C595989329D25
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            ADD CONSTRAINT FK_447C5959B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES innova_resource (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_447C5959B87FAB32 ON innova_step2resource (resourceNode_id)
        ");
    }
}