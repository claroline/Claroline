<?php

namespace Icap\BlogBundle\Migrations\pdo_pgsql;

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
            ADD banner_activate BOOLEAN DEFAULT 'true' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_background_color VARCHAR(255) DEFAULT '#FFFFFF' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_height SMALLINT DEFAULT '100' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_background_image VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_background_image_position SMALLINT DEFAULT '0' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_background_image_repeat SMALLINT DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP banner_activate
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP banner_background_color
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP banner_height
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP banner_background_image
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP banner_background_image_position
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP banner_background_image_repeat
        ");
    }
}