<?php

namespace Innova\PathBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/19 02:42:49
 */
class Version20130919144249 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD (
                stepOrder NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step MODIFY (
                parent VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP (deployable)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD (
                deployable NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step MODIFY (
                parent VARCHAR2(255) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP (stepOrder)
        ");
    }
}