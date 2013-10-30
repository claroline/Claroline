<?php

namespace Icap\BlogBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/30 03:34:37
 */
class Version20131030153435 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD (
                banner_activate NUMBER(1) DEFAULT '1' NOT NULL, 
                banner_background_color VARCHAR2(255) DEFAULT 'white' NOT NULL, 
                banner_height NUMBER(5) DEFAULT '100' NOT NULL, 
                banner_image VARCHAR2(255) DEFAULT NULL, 
                banner_image_position NUMBER(5) DEFAULT NULL, 
                banner_image_repeat NUMBER(5) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP (
                banner_activate, banner_background_color, 
                banner_height, banner_image, banner_image_position, 
                banner_image_repeat
            )
        ");
    }
}