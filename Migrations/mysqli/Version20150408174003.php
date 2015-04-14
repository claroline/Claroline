<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/08 05:40:05
 */
class Version20150408174003 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD is_notifiable TINYINT(1) DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_home_tab_roles
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP is_notifiable
        ");
    }
}