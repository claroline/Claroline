<?php

namespace Icap\BlogBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/31 01:49:50
 */
class Version20131031134948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD (
                banner_activate NUMBER(1) DEFAULT '1' NOT NULL, 
                banner_background_color VARCHAR2(255) DEFAULT '#FFFFFF' NOT NULL, 
                banner_height NUMBER(5) DEFAULT '100' NOT NULL, 
                banner_background_image VARCHAR2(255) DEFAULT NULL, 
                banner_background_image_position NUMBER(5) DEFAULT '0' NOT NULL, 
                banner_background_image_repeat NUMBER(5) DEFAULT '0' NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP (
                banner_activate, banner_background_color, 
                banner_height, banner_background_image, 
                banner_background_image_position, 
                banner_background_image_repeat
            )
        ");
    }
}