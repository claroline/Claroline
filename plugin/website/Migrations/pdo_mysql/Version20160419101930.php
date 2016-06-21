<?php

namespace Icap\WebsiteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/04/19 10:19:39
 */
class Version20160419101930 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website_page 
            ADD target SMALLINT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__website_options 
            ADD bgContentColor VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website_options 
            DROP bgContentColor
        ');
        $this->addSql('
            ALTER TABLE icap__website_page 
            DROP target
        ');
    }
}
