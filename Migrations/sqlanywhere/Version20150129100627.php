<?php

namespace Icap\PortfolioBundle\Migrations\sqlanywhere;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/29 10:06:30
 */
class Version20150129100627 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_slug_unique_idx ON icap__portfolio_widget_title (slug)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            ADD uri VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            DROP uri
        ");
        $this->addSql("
            DROP INDEX icap__portfolio_widget_title.portfolio_slug_unique_idx
        ");
    }
}