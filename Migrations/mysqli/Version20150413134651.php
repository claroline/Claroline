<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/13 01:46:55
 */
class Version20150413134651 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP is_notifiable
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD is_notifiable TINYINT(1) DEFAULT '0' NOT NULL
        ");
    }
}