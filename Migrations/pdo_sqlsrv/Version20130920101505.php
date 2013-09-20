<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

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
            sp_RENAME 'innova_step2resource.resourcenode_id', 
            'resource_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource ALTER COLUMN resource_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            DROP CONSTRAINT FK_447C5959B87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_447C5959B87FAB32'
            ) 
            ALTER TABLE innova_step2resource 
            DROP CONSTRAINT IDX_447C5959B87FAB32 ELSE 
            DROP INDEX IDX_447C5959B87FAB32 ON innova_step2resource
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
            sp_RENAME 'innova_step2resource.resource_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            DROP CONSTRAINT FK_447C595989329D25
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_447C595989329D25'
            ) 
            ALTER TABLE innova_step2resource 
            DROP CONSTRAINT IDX_447C595989329D25 ELSE 
            DROP INDEX IDX_447C595989329D25 ON innova_step2resource
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