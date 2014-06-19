<?php

namespace Icap\PortfolioBundle\Migrations\oci8;

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
            ADD (
                disposition NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD (
                col NUMBER(10) DEFAULT 1 NOT NULL, 
                \"row\" NUMBER(10) DEFAULT 1 NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio 
            DROP (disposition)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP (col, \"row\")
        ");
    }
}