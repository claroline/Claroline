<?php

namespace Innova\PathBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/20 09:55:25
 */
class Version20130920095525 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_step2resource (
                id SERIAL NOT NULL, 
                step_id INT DEFAULT NULL, 
                resourceOrder INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_447C595973B21E9C ON innova_step2resource (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_447C5959B87FAB32 ON innova_step2resource (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE innova_resource (
                id SERIAL NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description TEXT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            ADD CONSTRAINT FK_447C595973B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            ADD CONSTRAINT FK_447C5959B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES innova_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            ADD resourceOrder INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            DROP uuid
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD path_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP uuid
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567D96C566B ON innova_step (path_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step2resource 
            DROP CONSTRAINT FK_447C5959B87FAB32
        ");
        $this->addSql("
            DROP TABLE innova_step2resource
        ");
        $this->addSql("
            DROP TABLE innova_resource
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            ADD uuid VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD uuid VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP path_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F48567D96C566B
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567D96C566B
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            DROP resourceOrder
        ");
    }
}