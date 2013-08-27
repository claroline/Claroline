<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/27 01:37:19
 */
class Version20130827133715 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD COLUMN is_configurable_in_desktop SMALLINT NOT NULL RENAME has_options TO is_configurable_in_workspace
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD COLUMN has_options SMALLINT NOT NULL 
            DROP COLUMN is_configurable_in_workspace 
            DROP COLUMN is_configurable_in_desktop
        ");
    }
}