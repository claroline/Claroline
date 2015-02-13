<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/13 02:26:36
 */
class Version20150213142632 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_experience (
                id INT NOT NULL, 
                post NVARCHAR(255) NOT NULL, 
                companyName NVARCHAR(255) NOT NULL, 
                startDate DATETIME2(6), 
                endDate DATETIME2(6), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD CONSTRAINT FK_CD7379A3BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_widget_experience
        ");
    }
}