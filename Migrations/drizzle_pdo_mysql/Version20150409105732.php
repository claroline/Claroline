<?php

namespace Icap\PortfolioBundle\Migrations\drizzle_pdo_mysql;

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
            ALTER TABLE icap__portfolio_abstract_widget CHANGE size_x size_x INT DEFAULT 1 NOT NULL, 
            CHANGE size_y size_y INT DEFAULT 1 NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            ADD establishmentName VARCHAR(255) DEFAULT NULL, 
            ADD diploma VARCHAR(255) DEFAULT NULL, 
            CHANGE startDate startDate DATETIME NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD description TEXT DEFAULT NULL, 
            ADD website VARCHAR(255) DEFAULT NULL, 
            CHANGE startDate startDate DATETIME NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget CHANGE size_x size_x INT DEFAULT 0 NOT NULL, 
            CHANGE size_y size_y INT DEFAULT 0 NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            DROP description, 
            DROP website, 
            CHANGE startDate startDate DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            DROP establishmentName, 
            DROP diploma, 
            CHANGE startDate startDate DATETIME DEFAULT NULL
        ");
    }
}