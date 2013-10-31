<?php

namespace Icap\BlogBundle\Migrations\ibm_db2;

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
            ADD COLUMN banner_activate SMALLINT NOT NULL 
            ADD COLUMN banner_background_color VARCHAR(255) NOT NULL 
            ADD COLUMN banner_height SMALLINT NOT NULL 
            ADD COLUMN banner_background_image VARCHAR(255) DEFAULT NULL 
            ADD COLUMN banner_background_image_position SMALLINT NOT NULL 
            ADD COLUMN banner_background_image_repeat SMALLINT NOT NULL
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