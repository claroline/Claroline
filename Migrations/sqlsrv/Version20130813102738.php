<?php

namespace Claroline\AnnouncementBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/13 10:27:39
 */
class Version20130813102738 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement 
            DROP COLUMN announcement_order
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement 
            ADD announcement_order INT NOT NULL
        ");
    }
}