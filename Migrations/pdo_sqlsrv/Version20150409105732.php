<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlsrv;

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
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP CONSTRAINT DF_3E7AEFBB_81353A56
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER COLUMN size_x INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_81353A56 DEFAULT 1 FOR size_x
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP CONSTRAINT DF_3E7AEFBB_F6320AC0
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER COLUMN size_y INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_F6320AC0 DEFAULT 1 FOR size_y
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            ADD establishmentName NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            ADD diploma NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations ALTER COLUMN startDate DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD description VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD website NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience ALTER COLUMN startDate DATETIME2(6) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP CONSTRAINT DF_3E7AEFBB_81353A56
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER COLUMN size_x INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_81353A56 DEFAULT 0 FOR size_x
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP CONSTRAINT DF_3E7AEFBB_F6320AC0
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget ALTER COLUMN size_y INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT DF_3E7AEFBB_F6320AC0 DEFAULT 0 FOR size_y
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            DROP COLUMN description
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            DROP COLUMN website
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience ALTER COLUMN startDate DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            DROP COLUMN establishmentName
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            DROP COLUMN diploma
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations ALTER COLUMN startDate DATETIME2(6)
        ");
    }
}