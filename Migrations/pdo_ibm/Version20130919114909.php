<?php

namespace Innova\PathBundle\Migrations\pdo_ibm;

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
            ADD COLUMN \"order\" INTEGER NOT NULL 
            ADD COLUMN parent VARCHAR(255) NOT NULL 
            ADD COLUMN expanded SMALLINT NOT NULL 
            ADD COLUMN withTutor SMALLINT NOT NULL 
            ADD COLUMN withComputer SMALLINT NOT NULL 
            ADD COLUMN duration TIMESTAMP(0) NOT NULL 
            ADD COLUMN deployable SMALLINT NOT NULL ALTER uuid uuid VARCHAR(255) NOT NULL RENAME title TO instructions
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN \"order\" 
            DROP COLUMN parent 
            DROP COLUMN expanded 
            DROP COLUMN withTutor 
            DROP COLUMN withComputer 
            DROP COLUMN duration 
            DROP COLUMN deployable ALTER uuid uuid INTEGER NOT NULL RENAME instructions TO title
        ");
    }
}