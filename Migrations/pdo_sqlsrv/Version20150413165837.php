<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/13 04:58:40
 */
class Version20150413165837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD show_avatar BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD show_mail BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD show_phone BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD show_description BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP COLUMN show_avatar
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP COLUMN show_mail
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP COLUMN show_phone
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP COLUMN show_description
        ");
    }
}