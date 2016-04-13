<?php

namespace Icap\BlogBundle\Migrations\pdo_mysql;

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
            ADD slug VARCHAR(128) DEFAULT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_8BE67828989D9B62 ON icap__blog_tag (slug)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_8BE67828989D9B62 ON icap__blog_tag
        ');
        $this->addSql('
            ALTER TABLE icap__blog_tag 
            DROP slug
        ');
    }
}
