<?php

namespace Icap\PortfolioBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/13 04:58:39
 */
class Version20150413165837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD show_avatar TINYINT(1) NOT NULL, 
            ADD show_mail TINYINT(1) NOT NULL, 
            ADD show_phone TINYINT(1) NOT NULL, 
            ADD show_description TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP show_avatar, 
            DROP show_mail, 
            DROP show_phone, 
            DROP show_description
        ');
    }
}
