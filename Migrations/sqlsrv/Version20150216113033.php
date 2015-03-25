<?php

namespace Icap\PortfolioBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/16 11:30:36
 */
class Version20150216113033 extends AbstractMigration
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
        $this->addSql("
            ALTER TABLE icap__portfolio 
            DROP COLUMN disposition
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD size_x INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_81353A56 DEFAULT 0 FOR size_x
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD size_y INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_F6320AC0 DEFAULT 0 FOR size_y
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_widget_experience
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD disposition INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP COLUMN size_x
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP COLUMN size_y
        ");
    }
}