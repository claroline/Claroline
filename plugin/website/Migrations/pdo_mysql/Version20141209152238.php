<?php

namespace Icap\WebsiteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/12/09 03:22:40
 */
class Version20141209152238 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website 
            ADD homepage_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F8571EDDA FOREIGN KEY (homepage_id) 
            REFERENCES icap__website_page (id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_452309F8571EDDA ON icap__website (homepage_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website 
            DROP FOREIGN KEY FK_452309F8571EDDA
        ');
        $this->addSql('
            DROP INDEX UNIQ_452309F8571EDDA ON icap__website
        ');
        $this->addSql('
            ALTER TABLE icap__website 
            DROP homepage_id
        ');
    }
}
