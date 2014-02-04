<?php

namespace Innova\PathBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/28 03:21:20
 */
class Version20140128152119 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_step.steporder', 
            'step_order', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN step_order INT NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'innova_path.deployed', 
            'published', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_path ALTER COLUMN published BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_path.published', 
            'deployed', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_path ALTER COLUMN deployed BIT NOT NULL
        ");
        $this->addSql("
            sp_RENAME 'innova_step.step_order', 
            'stepOrder', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN stepOrder INT NOT NULL
        ");
    }
}