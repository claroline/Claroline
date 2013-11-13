<?php

namespace Icap\BlogBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/11/13 11:34:46
 */
class Version20131113113444 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER display_title 
            SET
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_activate 
            SET
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_background_color 
            SET
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_height 
            SET
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_background_image_position 
            SET
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_background_image_repeat 
            SET
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER display_title 
            SET 
                DEFAULT 'true'
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_activate 
            SET 
                DEFAULT 'true'
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_background_color 
            SET 
                DEFAULT '#FFFFFF'
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_height 
            SET 
                DEFAULT '100'
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_background_image_position 
            SET 
                DEFAULT 'left top'
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options ALTER banner_background_image_repeat 
            SET 
                DEFAULT 'no-repeat'
        ");
    }
}