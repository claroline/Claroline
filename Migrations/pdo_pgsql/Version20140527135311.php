<?php

namespace Innova\PathBundle\Migrations\pdo_pgsql;

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
            DROP image
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP withComputer
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD image VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD withComputer BOOLEAN NOT NULL
        ");
    }
}