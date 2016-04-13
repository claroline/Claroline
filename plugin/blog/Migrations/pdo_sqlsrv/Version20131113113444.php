<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/13 11:34:46
 */
class Version20131113113444 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP CONSTRAINT DF_D1AAC984_2A666380
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN display_title BIT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP CONSTRAINT DF_D1AAC984_B45FCA8E
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_activate BIT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP CONSTRAINT DF_D1AAC984_851F5EA8
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_background_color NVARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP CONSTRAINT DF_D1AAC984_76330FF9
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_height SMALLINT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP CONSTRAINT DF_D1AAC984_FBA21C33
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_background_image_position NVARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP CONSTRAINT DF_D1AAC984_A190A6AD
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_background_image_repeat NVARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN display_title BIT NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_2A666380 DEFAULT '1' FOR display_title
        ");
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_activate BIT NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_B45FCA8E DEFAULT '1' FOR banner_activate
        ");
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_background_color NVARCHAR(255) NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_851F5EA8 DEFAULT '#FFFFFF' FOR banner_background_color
        ");
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_height SMALLINT NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_76330FF9 DEFAULT '100' FOR banner_height
        ");
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_background_image_position NVARCHAR(255) NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_FBA21C33 DEFAULT 'left top' FOR banner_background_image_position
        ");
        $this->addSql('
            ALTER TABLE icap__blog_options ALTER COLUMN banner_background_image_repeat NVARCHAR(255) NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_A190A6AD DEFAULT 'no-repeat' FOR banner_background_image_repeat
        ");
    }
}
