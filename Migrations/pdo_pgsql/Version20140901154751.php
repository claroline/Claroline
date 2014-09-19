<?php

namespace Icap\WebsiteBundle\Migrations\pdo_pgsql;

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
            ADD root_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            DROP creation_date
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F879066886 FOREIGN KEY (root_id) 
            REFERENCES icap__website_page (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F879066886 ON icap__website (root_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website 
            DROP CONSTRAINT FK_452309F879066886
        ");
        $this->addSql("
            DROP INDEX UNIQ_452309F879066886
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            ADD creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            DROP root_id
        ");
    }
}