<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

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
            DROP INDEX IDX_447C595973B21E9C
        ");
        $this->addSql("
            DROP INDEX IDX_447C5959B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step2resource AS 
            SELECT id, 
            step_id, 
            resourceOrder, 
            resourceNode_id 
            FROM innova_step2resource
        ");
        $this->addSql("
            DROP TABLE innova_step2resource
        ");
        $this->addSql("
            CREATE TABLE innova_step2resource (
                id INTEGER NOT NULL, 
                step_id INTEGER DEFAULT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                resourceOrder INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_447C595973B21E9C FOREIGN KEY (step_id) 
                REFERENCES innova_step (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_447C595989329D25 FOREIGN KEY (resource_id) 
                REFERENCES innova_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step2resource (
                id, step_id, resourceOrder, resource_id
            ) 
            SELECT id, 
            step_id, 
            resourceOrder, 
            resourceNode_id 
            FROM __temp__innova_step2resource
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step2resource
        ");
        $this->addSql("
            CREATE INDEX IDX_447C595973B21E9C ON innova_step2resource (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_447C595989329D25 ON innova_step2resource (resource_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_447C595973B21E9C
        ");
        $this->addSql("
            DROP INDEX IDX_447C595989329D25
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step2resource AS 
            SELECT id, 
            step_id, 
            resource_id, 
            resourceOrder 
            FROM innova_step2resource
        ");
        $this->addSql("
            DROP TABLE innova_step2resource
        ");
        $this->addSql("
            CREATE TABLE innova_step2resource (
                id INTEGER NOT NULL, 
                step_id INTEGER DEFAULT NULL, 
                resourceOrder INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_447C595973B21E9C FOREIGN KEY (step_id) 
                REFERENCES innova_step (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_447C5959B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES innova_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step2resource (
                id, step_id, resourceNode_id, resourceOrder
            ) 
            SELECT id, 
            step_id, 
            resource_id, 
            resourceOrder 
            FROM __temp__innova_step2resource
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step2resource
        ");
        $this->addSql("
            CREATE INDEX IDX_447C595973B21E9C ON innova_step2resource (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_447C5959B87FAB32 ON innova_step2resource (resourceNode_id)
        ");
    }
}