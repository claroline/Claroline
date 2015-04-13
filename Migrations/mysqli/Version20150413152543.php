<?php

namespace Icap\PortfolioBundle\Migrations\mysqli;

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
            ALTER TABLE icap__portfolio_abstract_widget CHANGE col col INT DEFAULT 0 NOT NULL, 
            CHANGE row row INT DEFAULT 0 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget CHANGE col col INT DEFAULT 1 NOT NULL, 
            CHANGE row row INT DEFAULT 1 NOT NULL
        ");
    }
}