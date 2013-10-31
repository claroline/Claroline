<?php

namespace Icap\BlogBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/31 01:49:51
 */
class Version20131031134948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_activate BOOLEAN DEFAULT 'true' NOT NULL, 
            ADD banner_background_color VARCHAR(255) DEFAULT '#FFFFFF' NOT NULL, 
            ADD banner_height INT DEFAULT '100' NOT NULL, 
            ADD banner_background_image VARCHAR(255) DEFAULT NULL, 
            ADD banner_background_image_position INT DEFAULT '0' NOT NULL, 
            ADD banner_background_image_repeat INT DEFAULT '0' NOT NULL
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