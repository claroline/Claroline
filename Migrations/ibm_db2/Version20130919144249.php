<?php

namespace Innova\PathBundle\Migrations\ibm_db2;

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
            ADD COLUMN stepOrder INTEGER NOT NULL 
            DROP COLUMN deployable ALTER parent parent VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD COLUMN deployable SMALLINT NOT NULL 
            DROP COLUMN stepOrder ALTER parent parent VARCHAR(255) NOT NULL
        ");
    }
}