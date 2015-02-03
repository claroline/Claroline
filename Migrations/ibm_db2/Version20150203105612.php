<?php

namespace Icap\PortfolioBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/03 10:56:15
 */
class Version20150203105612 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_slug_unique_idx ON icap__portfolio_widget_title (slug)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            ADD COLUMN uri VARCHAR(255) DEFAULT NULL 
            ADD COLUMN uriLabel VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            DROP COLUMN uri 
            DROP COLUMN uriLabel
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD (
                'REORG TABLE icap__portfolio_widget_formations_resource'
            )
        ");
        $this->addSql("
            DROP INDEX portfolio_slug_unique_idx
        ");
    }
}