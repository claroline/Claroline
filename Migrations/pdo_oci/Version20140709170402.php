<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_oci;

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
            ALTER TABLE claro_announcement MODIFY (content CLOB DEFAULT NULL)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement MODIFY (
                content VARCHAR2(1023) DEFAULT NULL
            )
        ");
    }
}