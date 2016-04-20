<?php

namespace Icap\WebsiteBundle\Migrations\pdo_oci;

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
            ADD (
                totalWidth NUMBER(10) DEFAULT NULL, 
                sectionFontColor VARCHAR2(255) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website_options 
            DROP (totalWidth, sectionFontColor)
        ");
    }
}