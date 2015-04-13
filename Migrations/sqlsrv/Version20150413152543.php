<?php

namespace Icap\PortfolioBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/13 03:25:46
 */
class Version20150413152543 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP CONSTRAINT DF_3E7AEFBB_13B1F670
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER COLUMN col INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_13B1F670 DEFAULT 0 FOR col
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP CONSTRAINT DF_3E7AEFBB_8430F6DB
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER COLUMN row INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_8430F6DB DEFAULT 0 FOR row
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP CONSTRAINT DF_3E7AEFBB_13B1F670
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER COLUMN col INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_13B1F670 DEFAULT 1 FOR col
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP CONSTRAINT DF_3E7AEFBB_8430F6DB
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER COLUMN row INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_8430F6DB DEFAULT 1 FOR row
        ");
    }
}