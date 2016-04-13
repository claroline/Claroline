<?php

namespace Icap\BlogBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/13 11:34:46
 */
class Version20131113113444 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options MODIFY (
                display_title NUMBER(1) DEFAULT NULL, 
                banner_activate NUMBER(1) DEFAULT NULL, 
                banner_background_color VARCHAR2(255) DEFAULT NULL, 
                banner_height NUMBER(5) DEFAULT NULL, 
                banner_background_image_position VARCHAR2(255) DEFAULT NULL, 
                banner_background_image_repeat VARCHAR2(255) DEFAULT NULL
            )
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options MODIFY (
                display_title NUMBER(1) DEFAULT '1', 
                banner_activate NUMBER(1) DEFAULT '1', 
                banner_background_color VARCHAR2(255) DEFAULT '#FFFFFF', 
                banner_height NUMBER(5) DEFAULT '100', 
                banner_background_image_position VARCHAR2(255) DEFAULT 'left top', 
                banner_background_image_repeat VARCHAR2(255) DEFAULT 'no-repeat'
            )
        ");
    }
}
