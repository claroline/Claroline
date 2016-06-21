<?php

namespace Icap\BlogBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/13 11:34:45
 */
class Version20131113113444 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options CHANGE display_title display_title TINYINT(1) NOT NULL, 
            CHANGE banner_activate banner_activate TINYINT(1) NOT NULL, 
            CHANGE banner_background_color banner_background_color VARCHAR(255) NOT NULL, 
            CHANGE banner_height banner_height SMALLINT NOT NULL, 
            CHANGE banner_background_image_position banner_background_image_position VARCHAR(255) NOT NULL, 
            CHANGE banner_background_image_repeat banner_background_image_repeat VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options CHANGE display_title display_title TINYINT(1) DEFAULT '1' NOT NULL, 
            CHANGE banner_activate banner_activate TINYINT(1) DEFAULT '1' NOT NULL, 
            CHANGE banner_background_color banner_background_color VARCHAR(255) DEFAULT '#FFFFFF' NOT NULL, 
            CHANGE banner_height banner_height SMALLINT DEFAULT '100' NOT NULL, 
            CHANGE banner_background_image_position banner_background_image_position VARCHAR(255) DEFAULT 'left top' NOT NULL, 
            CHANGE banner_background_image_repeat banner_background_image_repeat VARCHAR(255) DEFAULT 'no-repeat' NOT NULL
        ");
    }
}
