<?php

namespace Innova\PathBundle\Migrations\pdo_pgsql;

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
            ALTER TABLE innova_step 
            ADD \"order\" INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD parent VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD expanded BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD withTutor BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD withComputer BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD duration TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD deployable BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER uuid TYPE VARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE innova_step RENAME COLUMN title TO instructions
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            DROP \"order\"
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP parent
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP expanded
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP withTutor
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP withComputer
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP duration
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP deployable
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER uuid TYPE INT
        ");
        $this->addSql("
            ALTER TABLE innova_step RENAME COLUMN instructions TO title
        ");
    }
}