<?php

namespace Icap\PortfolioBundle\Migrations\oci8;

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
            ALTER TABLE icap__portfolio_abstract_widget MODIFY (
                size_x NUMBER(10) DEFAULT 1, 
                size_y NUMBER(10) DEFAULT 1
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            ADD (
                establishmentName VARCHAR2(255) DEFAULT NULL, 
                diploma VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations MODIFY (
                startDate TIMESTAMP(0) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD (
                description CLOB DEFAULT NULL, 
                website VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience MODIFY (
                startDate TIMESTAMP(0) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget MODIFY (
                size_x NUMBER(10) DEFAULT 0, 
                size_y NUMBER(10) DEFAULT 0
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience MODIFY (
                startDate TIMESTAMP(0) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            DROP (description, website)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations MODIFY (
                startDate TIMESTAMP(0) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations 
            DROP (establishmentName, diploma)
        ");
    }
}