<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlsrv;

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
            ADD disposition INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD col INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_13B1F670 DEFAULT 1 FOR col
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD row INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_8430F6DB DEFAULT 1 FOR row
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
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP COLUMN row
        ");
    }
}