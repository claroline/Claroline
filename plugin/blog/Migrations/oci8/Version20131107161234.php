<?php

namespace Icap\BlogBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/07 04:12:35
 */
class Version20131107161234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD (
                display_title NUMBER(1) DEFAULT '1' NOT NULL, 
                banner_activate NUMBER(1) DEFAULT '1' NOT NULL, 
                banner_background_color VARCHAR2(255) DEFAULT '#FFFFFF' NOT NULL, 
                banner_height NUMBER(5) DEFAULT '100' NOT NULL, 
                banner_background_image VARCHAR2(255) DEFAULT NULL, 
                banner_background_image_position VARCHAR2(255) DEFAULT 'left top' NOT NULL, 
                banner_background_image_repeat VARCHAR2(255) DEFAULT 'no-repeat' NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP (
                display_title, banner_activate, banner_background_color, 
                banner_height, banner_background_image, 
                banner_background_image_position, 
                banner_background_image_repeat
            )
        ');
    }
}
