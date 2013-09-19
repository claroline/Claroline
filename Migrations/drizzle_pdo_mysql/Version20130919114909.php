<?php

namespace Innova\PathBundle\Migrations\drizzle_pdo_mysql;

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
            ADD `order` INT NOT NULL, 
            ADD parent VARCHAR(255) NOT NULL, 
            ADD expanded BOOLEAN NOT NULL, 
            ADD withTutor BOOLEAN NOT NULL, 
            ADD withComputer BOOLEAN NOT NULL, 
            ADD duration DATETIME NOT NULL, 
            ADD deployable BOOLEAN NOT NULL, 
            CHANGE uuid uuid VARCHAR(255) NOT NULL, 
            CHANGE title instructions TEXT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            DROP `order`, 
            DROP parent, 
            DROP expanded, 
            DROP withTutor, 
            DROP withComputer, 
            DROP duration, 
            DROP deployable, 
            CHANGE uuid uuid INT NOT NULL, 
            CHANGE instructions title TEXT NOT NULL
        ");
    }
}