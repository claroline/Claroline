<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_step2resourceNode (
                id INT IDENTITY NOT NULL, 
                step_id INT, 
                resourceOrder INT NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11F73B21E9C ON innova_step2resourceNode (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11FB87FAB32 ON innova_step2resourceNode (resourceNode_id)
        ");
        $this->addSql("
            CREATE TABLE innova_user2path (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                path_id INT NOT NULL, 
                status INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5A76ED395 ON innova_user2path (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D4590E5D96C566B ON innova_user2path (path_id)
        ");
        $this->addSql("
            CREATE TABLE innova_pathtemplate (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX) NOT NULL, 
                step VARCHAR(MAX) NOT NULL, 
                [user] NVARCHAR(255) NOT NULL, 
                edit_date DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_stepWhere (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_stepWho (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_nonDigitalResource (
                id INT IDENTITY NOT NULL, 
                description VARCHAR(MAX) NOT NULL, 
                type NVARCHAR(255) NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_305E9E56B87FAB32 ON innova_nonDigitalResource (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE innova_step (
                id INT IDENTITY NOT NULL, 
                path_id INT, 
                stepOrder INT NOT NULL, 
                parent NVARCHAR(255), 
                expanded BIT NOT NULL, 
                instructions VARCHAR(MAX) NOT NULL, 
                withTutor BIT NOT NULL, 
                withComputer BIT NOT NULL, 
                duration DATETIME2(6) NOT NULL, 
                stepType_id INT, 
                stepWho_id INT, 
                stepWhere_id INT, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567D96C566B ON innova_step (path_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567DEDC9FF6 ON innova_step (stepType_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F4856765544574 ON innova_step (stepWho_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F485678FE76F3 ON innova_step (stepWhere_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_86F48567B87FAB32 ON innova_step (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
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
            DROP CONSTRAINT FK_86F48567DEDC9FF6
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F485678FE76F3
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F4856765544574
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode 
            DROP CONSTRAINT FK_21EA11F73B21E9C
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