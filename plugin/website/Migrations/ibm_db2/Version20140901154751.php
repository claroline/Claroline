<?php

namespace Icap\WebsiteBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/01 03:47:53
 */
class Version20140901154751 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website 
            ADD COLUMN root_id INTEGER DEFAULT NULL 
            DROP COLUMN creation_date
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F879066886 FOREIGN KEY (root_id) 
            REFERENCES icap__website_page (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F879066886 ON icap__website (root_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website 
            ADD COLUMN creation_date TIMESTAMP(0) NOT NULL 
            DROP COLUMN root_id
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            DROP FOREIGN KEY FK_452309F879066886
        ");
        $this->addSql("
            DROP INDEX UNIQ_452309F879066886
        ");
    }
}