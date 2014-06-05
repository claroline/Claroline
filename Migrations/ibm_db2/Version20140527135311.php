<?php

namespace Innova\PathBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/27 01:53:12
 */
class Version20140527135311 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN image 
            DROP COLUMN withComputer
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD COLUMN image VARCHAR(255) DEFAULT NULL 
            ADD COLUMN withComputer SMALLINT NOT NULL
        ");
    }
}