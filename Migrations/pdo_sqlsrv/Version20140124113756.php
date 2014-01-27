<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/24 11:37:57
 */
class Version20140124113756 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_step.step_order', 
            'stepOrder', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN stepOrder INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_step.steporder', 
            'step_order', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_step ALTER COLUMN step_order INT NOT NULL
        ");
    }
}