<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/27 01:37:20
 */
class Version20130827133715 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_tools.has_options', 
            'is_configurable_in_workspace', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD is_configurable_in_desktop BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_tools ALTER COLUMN is_configurable_in_workspace BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD has_options BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP COLUMN is_configurable_in_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP COLUMN is_configurable_in_desktop
        ");
    }
}