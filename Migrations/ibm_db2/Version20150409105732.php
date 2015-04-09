<?php

namespace Icap\PortfolioBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/09 10:57:36
 */
class Version20150409105732 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER size_x size_x INTEGER NOT NULL ALTER size_y size_y INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            ADD COLUMN establishmentName VARCHAR(255) DEFAULT NULL 
            ADD COLUMN diploma VARCHAR(255) DEFAULT NULL ALTER startDate startDate TIMESTAMP(0) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD COLUMN description CLOB(1M) DEFAULT NULL 
            ADD COLUMN website VARCHAR(255) DEFAULT NULL ALTER startDate startDate TIMESTAMP(0) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER size_x size_x INTEGER NOT NULL ALTER size_y size_y INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            DROP COLUMN description 
            DROP COLUMN website ALTER startDate startDate TIMESTAMP(0) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            DROP COLUMN establishmentName 
            DROP COLUMN diploma ALTER startDate startDate TIMESTAMP(0) DEFAULT NULL
        ");
    }
}