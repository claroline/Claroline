<?php

namespace Icap\PortfolioBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/16 11:30:35
 */
class Version20150216113033 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_experience (
                id NUMBER(10) NOT NULL, 
                post VARCHAR2(255) NOT NULL, 
                companyName VARCHAR2(255) NOT NULL, 
                startDate TIMESTAMP(0) DEFAULT NULL NULL, 
                endDate TIMESTAMP(0) DEFAULT NULL NULL, 
                PRIMARY KEY(id)
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
            DROP (disposition)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD (
                size_x NUMBER(10) DEFAULT 0 NOT NULL, 
                size_y NUMBER(10) DEFAULT 0 NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_widget_experience
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD (
                disposition NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP (size_x, size_y)
        ");
    }
}