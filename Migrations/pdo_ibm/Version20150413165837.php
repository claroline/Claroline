<?php

namespace Icap\PortfolioBundle\Migrations\pdo_ibm;

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
            ADD COLUMN show_avatar SMALLINT NOT NULL 
            ADD COLUMN show_mail SMALLINT NOT NULL 
            ADD COLUMN show_phone SMALLINT NOT NULL 
            ADD COLUMN show_description SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP COLUMN show_avatar 
            DROP COLUMN show_mail 
            DROP COLUMN show_phone 
            DROP COLUMN show_description
        ");
    }
}