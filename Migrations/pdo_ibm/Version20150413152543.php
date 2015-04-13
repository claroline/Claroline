<?php

namespace Icap\PortfolioBundle\Migrations\pdo_ibm;

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
            ALTER TABLE icap__portfolio_abstract_widget ALTER col col INTEGER NOT NULL ALTER \"row\" \"row\" INTEGER NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER col col INTEGER NOT NULL ALTER \"row\" \"row\" INTEGER NOT NULL
        ");
    }
}