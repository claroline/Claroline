<?php

namespace Innova\PathBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 11:11:17
 */
class Version20130923111117 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_stepType (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_step2resourceNode (
                id INT AUTO_INCREMENT NOT NULL, 
                step_id INT DEFAULT NULL, 
                resourceOrder INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_21EA11F73B21E9C (step_id), 
                INDEX IDX_21EA11FB87FAB32 (resourceNode_id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_user2path (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                path_id INT NOT NULL, 
                status INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_2D4590E5A76ED395 (user_id), 
                INDEX IDX_2D4590E5D96C566B (path_id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_pathtemplate (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description TEXT NOT NULL, 
                step TEXT NOT NULL, 
                `user` VARCHAR(255) NOT NULL, 
                edit_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_stepWhere (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_stepWho (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_nonDigitalResource (
                id INT AUTO_INCREMENT NOT NULL, 
                description TEXT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_305E9E56B87FAB32 (resourceNode_id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id INT AUTO_INCREMENT NOT NULL, 
                path_id INT DEFAULT NULL, 
                stepOrder INT NOT NULL, 
                parent VARCHAR(255) DEFAULT NULL, 
                expanded BOOLEAN NOT NULL, 
                instructions TEXT NOT NULL, 
                withTutor BOOLEAN NOT NULL, 
                withComputer BOOLEAN NOT NULL, 
                duration DATETIME NOT NULL, 
                stepType_id INT DEFAULT NULL, 
                stepWho_id INT DEFAULT NULL, 
                stepWhere_id INT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_86F48567D96C566B (path_id), 
                INDEX IDX_86F48567DEDC9FF6 (stepType_id), 
                INDEX IDX_86F4856765544574 (stepWho_id), 
                INDEX IDX_86F485678FE76F3 (stepWhere_id), 
                UNIQUE INDEX UNIQ_86F48567B87FAB32 (resourceNode_id)
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            ADD CONSTRAINT FK_21EA11F73B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            ADD CONSTRAINT FK_21EA11FB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE innova_user2path 
            ADD CONSTRAINT FK_2D4590E5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_user2path 
            ADD CONSTRAINT FK_2D4590E5D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            ADD CONSTRAINT FK_305E9E56B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
            REFERENCES innova_stepType (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856765544574 FOREIGN KEY (stepWho_id) 
            REFERENCES innova_stepWho (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F485678FE76F3 FOREIGN KEY (stepWhere_id) 
            REFERENCES innova_stepWhere (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567DEDC9FF6
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F485678FE76F3
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F4856765544574
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            DROP FOREIGN KEY FK_21EA11F73B21E9C
        ");
        $this->addSql("
            DROP TABLE innova_stepType
        ");
        $this->addSql("
            DROP TABLE innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE innova_user2path
        ");
        $this->addSql("
            DROP TABLE innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE innova_stepWhere
        ");
        $this->addSql("
            DROP TABLE innova_stepWho
        ");
        $this->addSql("
            DROP TABLE innova_nonDigitalResource
        ");
        $this->addSql("
            DROP TABLE innova_step
        ");
    }
}