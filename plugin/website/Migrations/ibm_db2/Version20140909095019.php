<?php

namespace Icap\WebsiteBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/09 09:50:20
 */
class Version20140909095019 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website_options 
            ADD COLUMN totalWidth INTEGER DEFAULT NULL 
            ADD COLUMN sectionFontColor VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website_options 
            DROP COLUMN totalWidth 
            DROP COLUMN sectionFontColor
        ");
    }
}