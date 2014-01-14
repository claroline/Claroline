<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/14 11:50:16
 */
class Version20140114115015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_step.instructions', 
            'description', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN expanded
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN stepType_id
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN duration DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN description VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP CONSTRAINT FK_86F48567DEDC9FF6
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_86F48567DEDC9FF6'
            ) 
            ALTER TABLE innova_step 
            DROP CONSTRAINT IDX_86F48567DEDC9FF6 ELSE 
            DROP INDEX IDX_86F48567DEDC9FF6 ON innova_step
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_step.description', 
            'instructions', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD expanded BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD stepType_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN duration DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN instructions VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
            REFERENCES innova_stepType (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567DEDC9FF6 ON innova_step (stepType_id)
        ");
    }
}