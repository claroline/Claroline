<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/09 05:04:03
 */
class Version20140709170402 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement ALTER content TYPE TEXT
        ");
        $this->addSql("
            ALTER TABLE claro_announcement ALTER content TYPE TEXT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement ALTER content TYPE VARCHAR(1023)
        ");
    }
}