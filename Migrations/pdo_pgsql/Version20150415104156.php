<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/15 10:41:58
 */
class Version20150415104156 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_home_tab 
            ADD icon VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            ADD icon VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_home_tab 
            DROP icon
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            DROP icon
        ");
    }
}