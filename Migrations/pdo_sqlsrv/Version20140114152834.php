<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/14 03:28:35
 */
class Version20140114152834 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_pathtemplate.step', 
            'structure', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate ALTER COLUMN structure VARCHAR(MAX) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_pathtemplate.structure', 
            'step', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_pathtemplate ALTER COLUMN step VARCHAR(MAX) NOT NULL
        ");
    }
}