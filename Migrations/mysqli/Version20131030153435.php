<?php

namespace Icap\BlogBundle\Migrations\mysqli;

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
            ADD banner_activate TINYINT(1) DEFAULT '1' NOT NULL, 
            ADD banner_background_color VARCHAR(255) DEFAULT 'white' NOT NULL, 
            ADD banner_height SMALLINT DEFAULT '100' NOT NULL, 
            ADD banner_image VARCHAR(255) DEFAULT NULL, 
            ADD banner_image_position SMALLINT DEFAULT NULL, 
            ADD banner_image_repeat SMALLINT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP banner_activate, 
            DROP banner_background_color, 
            DROP banner_height, 
            DROP banner_image, 
            DROP banner_image_position, 
            DROP banner_image_repeat
        ");
    }
}