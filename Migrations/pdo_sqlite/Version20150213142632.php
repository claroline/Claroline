<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/13 02:26:35
 */
class Version20150213142632 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_experience (
                id INTEGER NOT NULL, 
                post VARCHAR(255) NOT NULL, 
                companyName VARCHAR(255) NOT NULL, 
                startDate DATETIME DEFAULT NULL, 
                endDate DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_widget_experience
        ");
    }
}