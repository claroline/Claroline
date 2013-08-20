<?php

namespace Claroline\AnnouncementBundle\Migrations\mysqli;

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
            ALTER TABLE claro_announcement CHANGE creation_date creation_date DATETIME NOT NULL, 
            CHANGE publication_date publication_date DATETIME DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement CHANGE creation_date creation_date DATE NOT NULL, 
            CHANGE publication_date publication_date DATE DEFAULT NULL
        ");
    }
}