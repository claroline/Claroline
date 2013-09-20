<?php

namespace Innova\PathBundle\Migrations\mysqli;

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
                id INT AUTO_INCREMENT NOT NULL, 
                step_id INT DEFAULT NULL, 
                resourceOrder INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                INDEX IDX_447C595973B21E9C (step_id), 
                INDEX IDX_447C5959B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE innova_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            DROP uuid
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD path_id INT DEFAULT NULL, 
            DROP uuid
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
            DROP FOREIGN KEY FK_447C5959B87FAB32
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
            DROP FOREIGN KEY FK_86F48567D96C566B
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567D96C566B ON innova_step
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD uuid VARCHAR(255) NOT NULL, 
            DROP path_id
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            DROP resourceOrder
        ");
    }
}