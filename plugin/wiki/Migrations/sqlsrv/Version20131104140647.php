<?php

namespace Icap\WikiBundle\Migrations\sqlsrv;

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
            ADD deleted BIT
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            ADD deletion_date DATETIME2(6)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP COLUMN deleted
        ");
        $this->addSql("
            ALTER TABLE icap__wiki_section 
            DROP COLUMN deletion_date
        ");
    }
}