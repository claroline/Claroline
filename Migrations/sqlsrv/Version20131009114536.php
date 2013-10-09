<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/09 11:45:37
 */
class Version20131009114536 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_path 
            ADD deployed BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            ADD modified BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_path 
            DROP COLUMN deployed
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            DROP COLUMN modified
        ");
    }
}