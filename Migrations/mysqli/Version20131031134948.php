<?php

namespace Icap\BlogBundle\Migrations\mysqli;

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
            ADD banner_activate TINYINT(1) DEFAULT '1' NOT NULL, 
            ADD banner_background_color VARCHAR(255) DEFAULT '#FFFFFF' NOT NULL, 
            ADD banner_height SMALLINT DEFAULT '100' NOT NULL, 
            ADD banner_background_image VARCHAR(255) DEFAULT NULL, 
            ADD banner_background_image_position SMALLINT DEFAULT '0' NOT NULL, 
            ADD banner_background_image_repeat SMALLINT DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP banner_activate, 
            DROP banner_background_color, 
            DROP banner_height, 
            DROP banner_background_image, 
            DROP banner_background_image_position, 
            DROP banner_background_image_repeat
        ");
    }
}