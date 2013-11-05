<?php

namespace Icap\BlogBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/11/05 03:38:00
 */
class Version20131105153757 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD COLUMN banner_activate SMALLINT NOT NULL 
            ADD COLUMN banner_background_color VARCHAR(255) NOT NULL 
            ADD COLUMN banner_height SMALLINT NOT NULL 
            ADD COLUMN banner_background_image VARCHAR(255) DEFAULT NULL 
            ADD COLUMN banner_background_image_position VARCHAR(255) NOT NULL 
            ADD COLUMN banner_background_image_repeat VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP COLUMN banner_activate 
            DROP COLUMN banner_background_color 
            DROP COLUMN banner_height 
            DROP COLUMN banner_background_image 
            DROP COLUMN banner_background_image_position 
            DROP COLUMN banner_background_image_repeat
        ");
    }
}