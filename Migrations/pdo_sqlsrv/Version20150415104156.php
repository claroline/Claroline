<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/15 10:41:59
 */
class Version20150415104156 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_home_tab 
            ADD icon NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            ADD icon NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_home_tab 
            DROP COLUMN icon
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            DROP COLUMN icon
        ");
    }
}