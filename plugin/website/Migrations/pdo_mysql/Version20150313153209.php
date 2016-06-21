<?php

namespace Icap\WebsiteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/13 03:32:10
 */
class Version20150313153209 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website 
            DROP FOREIGN KEY FK_452309F8571EDDA
        ');
        $this->addSql('
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F8571EDDA FOREIGN KEY (homepage_id) 
            REFERENCES icap__website_page (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website 
            DROP FOREIGN KEY FK_452309F8571EDDA
        ');
        $this->addSql('
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F8571EDDA FOREIGN KEY (homepage_id) 
            REFERENCES icap__website_page (id)
        ');
    }
}
