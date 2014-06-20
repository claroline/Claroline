<?php

namespace Icap\PortfolioBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/19 11:09:03
 */
class Version20140619110902 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD COLUMN disposition INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD COLUMN col INTEGER NOT NULL 
            ADD COLUMN \"row\" INTEGER NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio 
            DROP COLUMN disposition
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP COLUMN col 
            DROP COLUMN \"row\"
        ");
    }
}