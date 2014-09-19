<?php

namespace Icap\WebsiteBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/02 01:42:57
 */
class Version20140902134256 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website 
            ADD options_id INT
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F83ADB05F1 FOREIGN KEY (options_id) 
            REFERENCES icap__website_options (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F83ADB05F1 ON icap__website (options_id) 
            WHERE options_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website 
            DROP COLUMN options_id
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            DROP CONSTRAINT FK_452309F83ADB05F1
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_452309F83ADB05F1'
            ) 
            ALTER TABLE icap__website 
            DROP CONSTRAINT UNIQ_452309F83ADB05F1 ELSE 
            DROP INDEX UNIQ_452309F83ADB05F1 ON icap__website
        ");
    }
}