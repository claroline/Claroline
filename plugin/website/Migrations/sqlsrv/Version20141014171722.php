<?php

namespace Icap\WebsiteBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/14 05:17:24
 */
class Version20141014171722 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD resourceNodeType NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website_page 
            DROP COLUMN resourceNodeType
        ");
    }
}