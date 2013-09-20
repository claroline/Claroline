<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                step_id INT, 
                resourceOrder INT NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX) NOT NULL, 
                type NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            ADD CONSTRAINT FK_447C595973B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2resource 
            ADD CONSTRAINT FK_447C5959B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES innova_resource (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            ADD resourceOrder INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            DROP COLUMN uuid
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD path_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN uuid
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id)
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
            ADD uuid NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD uuid NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN path_id
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F48567D96C566B
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_86F48567D96C566B'
            ) 
            ALTER TABLE innova_step 
            DROP CONSTRAINT IDX_86F48567D96C566B ELSE 
            DROP INDEX IDX_86F48567D96C566B ON innova_step
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            DROP COLUMN resourceOrder
        ");
    }
}