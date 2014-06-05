<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

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
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN withComputer
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD image NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD withComputer BIT NOT NULL
        ");
    }
}