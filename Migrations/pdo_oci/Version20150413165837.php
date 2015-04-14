<?php

namespace Icap\PortfolioBundle\Migrations\pdo_oci;

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
            ADD (
                show_avatar NUMBER(1) NOT NULL, 
                show_mail NUMBER(1) NOT NULL, 
                show_phone NUMBER(1) NOT NULL, 
                show_description NUMBER(1) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP (
                show_avatar, show_mail, show_phone, 
                show_description
            )
        ");
    }
}