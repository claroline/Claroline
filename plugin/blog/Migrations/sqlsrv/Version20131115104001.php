<?php

namespace Icap\BlogBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/15 10:40:03
 */
class Version20131115104001 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_tag 
            ADD slug NVARCHAR(128)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_8BE67828989D9B62 ON icap__blog_tag (slug) 
            WHERE slug IS NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_tag 
            DROP COLUMN slug
        ');
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_8BE67828989D9B62'
            ) 
            ALTER TABLE icap__blog_tag 
            DROP CONSTRAINT UNIQ_8BE67828989D9B62 ELSE 
            DROP INDEX UNIQ_8BE67828989D9B62 ON icap__blog_tag
        ");
    }
}
