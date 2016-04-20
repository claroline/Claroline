<?php

namespace Icap\WebsiteBundle\Migrations\pdo_sqlsrv;

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
            ADD root_id INT
        ");
        $this->addSql("
            ALTER TABLE icap__website 
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
            WHERE root_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website 
            ADD creation_date DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            DROP COLUMN root_id
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            DROP CONSTRAINT FK_452309F879066886
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_452309F879066886'
            ) 
            ALTER TABLE icap__website 
            DROP CONSTRAINT UNIQ_452309F879066886 ELSE 
            DROP INDEX UNIQ_452309F879066886 ON icap__website
        ");
    }
}