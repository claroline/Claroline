<?php

namespace Icap\PortfolioBundle\Migrations\pdo_pgsql;

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
            ALTER TABLE icap__portfolio_abstract_widget ALTER size_x 
            SET 
                DEFAULT 1
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER size_y 
            SET 
                DEFAULT 1
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            ADD establishmentName VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            ADD diploma VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations ALTER startDate 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD description TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD website VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience ALTER startDate 
            SET 
                NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER size_x 
            SET 
                DEFAULT 0
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER size_y 
            SET 
                DEFAULT 0
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            DROP description
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            DROP website
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience ALTER startDate 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            DROP establishmentName
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            DROP diploma
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations ALTER startDate 
            DROP NOT NULL
        ");
    }
}