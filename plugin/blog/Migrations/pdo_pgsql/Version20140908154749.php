<?php

namespace Icap\BlogBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/09/08 03:47:50
 */
class Version20140908154749 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_post ALTER modification_date 
            DROP NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post ALTER viewCounter 
            SET 
                DEFAULT 0
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_post ALTER modification_date 
            SET 
                NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post ALTER viewCounter 
            DROP DEFAULT
        ');
    }
}
