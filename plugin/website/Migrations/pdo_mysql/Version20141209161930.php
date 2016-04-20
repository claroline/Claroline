<?php

namespace Icap\WebsiteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/12/09 04:19:31
 */
class Version20141209161930 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website_page 
            ADD isHomepage TINYINT(1) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website_page 
            DROP isHomepage
        ');
    }
}
