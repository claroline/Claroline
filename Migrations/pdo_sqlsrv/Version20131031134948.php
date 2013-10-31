<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlsrv;

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
            ADD banner_background_image_position SMALLINT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_FBA21C33 DEFAULT '0' FOR banner_background_image_position
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD banner_background_image_repeat SMALLINT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_A190A6AD DEFAULT '0' FOR banner_background_image_repeat
        ");
    }

    public function down(Schema $schema)
    {
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