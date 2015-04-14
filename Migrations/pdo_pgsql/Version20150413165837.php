<?php

namespace Icap\PortfolioBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/13 04:58:39
 */
class Version20150413165837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD show_avatar BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD show_mail BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD show_phone BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD show_description BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP show_avatar
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP show_mail
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP show_phone
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP show_description
        ");
    }
}