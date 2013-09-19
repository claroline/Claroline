<?php

namespace Innova\PathBundle\Migrations\oci8;

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
            ADD (
                \"order\" NUMBER(10) NOT NULL, 
                parent VARCHAR2(255) NOT NULL, 
                expanded NUMBER(1) NOT NULL, 
                withTutor NUMBER(1) NOT NULL, 
                withComputer NUMBER(1) NOT NULL, 
                duration TIMESTAMP(0) NOT NULL, 
                deployable NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step MODIFY (
                uuid VARCHAR2(255) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step RENAME COLUMN title TO instructions
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step MODIFY (
                uuid NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step RENAME COLUMN instructions TO title
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP (
                \"order\", parent, expanded, withTutor, 
                withComputer, duration, deployable
            )
        ");
    }
}