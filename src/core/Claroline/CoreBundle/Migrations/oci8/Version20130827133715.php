<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/27 01:37:18
 */
class Version20130827133715 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD (
                is_configurable_in_desktop NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_tools RENAME COLUMN has_options TO is_configurable_in_workspace
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD (
                has_options NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP (
                is_configurable_in_workspace, is_configurable_in_desktop
            )
        ");
    }
}