<?php

namespace Claroline\AnnouncementBundle\Migrations\ibm_db2;

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
            ALTER TABLE claro_announcement ALTER creation_date creation_date TIMESTAMP(0) NOT NULL ALTER publication_date publication_date TIMESTAMP(0) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement ALTER creation_date creation_date DATE NOT NULL ALTER publication_date publication_date DATE DEFAULT NULL
        ");
    }
}