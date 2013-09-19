<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/19 11:49:09
 */
class Version20130919114909 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_step.title', 
            'instructions', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD [order] INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD parent NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD expanded BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD withTutor BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD withComputer BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD duration DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD deployable BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN uuid NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN instructions VARCHAR(MAX) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_step.instructions', 
            'title', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN [order]
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN parent
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN expanded
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN withTutor
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN withComputer
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN duration
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN deployable
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN uuid INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN title VARCHAR(MAX) NOT NULL
        ");
    }
}