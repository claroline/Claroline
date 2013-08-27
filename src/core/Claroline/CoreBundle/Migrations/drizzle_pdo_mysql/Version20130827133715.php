<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
            ADD is_configurable_in_desktop BOOLEAN NOT NULL, 
            CHANGE has_options is_configurable_in_workspace BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD has_options BOOLEAN NOT NULL, 
            DROP is_configurable_in_workspace, 
            DROP is_configurable_in_desktop
        ");
    }
}