<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/19 03:53:28
 */
class Version20130819155327 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement ALTER creation_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        ");
        $this->addSql("
            ALTER TABLE claro_announcement ALTER publication_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement ALTER creation_date TYPE DATE
        ");
        $this->addSql("
            ALTER TABLE claro_announcement ALTER publication_date TYPE DATE
        ");
    }
}