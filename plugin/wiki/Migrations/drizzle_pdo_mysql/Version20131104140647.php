<?php

namespace Icap\WikiBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/11/04 02:06:48
 */
class Version20131104140647 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD deleted BOOLEAN DEFAULT NULL, 
            ADD deletion_date DATETIME DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP deleted, 
            DROP deletion_date
        ");
    }
}