<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/14 12:39:49
 */
class Version20140114123948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_path.path', 
            'structure', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_path ALTER COLUMN structure VARCHAR(MAX) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_path.structure', 
            'path', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_path ALTER COLUMN path VARCHAR(MAX) NOT NULL
        ");
    }
}