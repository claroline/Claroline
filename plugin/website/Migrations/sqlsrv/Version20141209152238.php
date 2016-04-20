<?php

namespace Icap\WebsiteBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/09 03:22:40
 */
class Version20141209152238 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website 
            ADD homepage_id INT
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F8571EDDA FOREIGN KEY (homepage_id) 
            REFERENCES icap__website_page (id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F8571EDDA ON icap__website (homepage_id) 
            WHERE homepage_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website 
            DROP COLUMN homepage_id
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            DROP CONSTRAINT FK_452309F8571EDDA
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_452309F8571EDDA'
            ) 
            ALTER TABLE icap__website 
            DROP CONSTRAINT UNIQ_452309F8571EDDA ELSE 
            DROP INDEX UNIQ_452309F8571EDDA ON icap__website
        ");
    }
}