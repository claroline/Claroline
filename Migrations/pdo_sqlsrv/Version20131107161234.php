<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/11/07 04:12:36
 */
class Version20131107161234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD display_title BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_2A666380 DEFAULT '1' FOR display_title
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_activate BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_B45FCA8E DEFAULT '1' FOR banner_activate
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_background_color NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_851F5EA8 DEFAULT '#FFFFFF' FOR banner_background_color
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_height SMALLINT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_76330FF9 DEFAULT '100' FOR banner_height
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_background_image NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_background_image_position NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_FBA21C33 DEFAULT 'left top' FOR banner_background_image_position
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_background_image_repeat NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_A190A6AD DEFAULT 'no-repeat' FOR banner_background_image_repeat
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP COLUMN display_title
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP COLUMN banner_activate
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP COLUMN banner_background_color
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP COLUMN banner_height
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP COLUMN banner_background_image
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP COLUMN banner_background_image_position
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            DROP COLUMN banner_background_image_repeat
        ");
    }
}