<?php

namespace Icap\WebsiteBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/07 03:19:14
 */
class Version20140707151913 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__website_page (
                id INT IDENTITY NOT NULL, 
                resource_node_id INT, 
                website_id INT NOT NULL, 
                parent_id INT, 
                visible BIT NOT NULL, 
                creation_date DATETIME2(6) NOT NULL, 
                title NVARCHAR(255) NOT NULL, 
                richText VARCHAR(MAX), 
                url NVARCHAR(255), 
                isSection BIT NOT NULL, 
                description NVARCHAR(255), 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FB66D1D41BAD783F ON icap__website_page (resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FB66D1D418F45C82 ON icap__website_page (website_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FB66D1D4727ACA70 ON icap__website_page (parent_id)
        ");
        $this->addSql("
            CREATE TABLE icap__website_options (
                id INT IDENTITY NOT NULL, 
                website_id INT, 
                copyrightEnabled BIT NOT NULL, 
                copyrightText NVARCHAR(255), 
                analyticsProvider NVARCHAR(255), 
                analyticsAccountId NVARCHAR(255), 
                cssCode VARCHAR(MAX), 
                bgColor NVARCHAR(255), 
                bgImage NVARCHAR(255), 
                bgRepeat NVARCHAR(255), 
                bgPosition NVARCHAR(255), 
                bannerBgColor NVARCHAR(255), 
                bannerBgImage NVARCHAR(255), 
                bannerBgRepeat NVARCHAR(255), 
                bannerBgPosition NVARCHAR(255), 
                bannerHeight INT, 
                bannerEnabled BIT NOT NULL, 
                bannerText VARCHAR(MAX), 
                footerBgColor NVARCHAR(255), 
                footerBgImage NVARCHAR(255), 
                footerBgRepeat NVARCHAR(255), 
                footerBgPosition NVARCHAR(255), 
                footerHeight INT, 
                footerEnabled BIT NOT NULL, 
                footerText VARCHAR(MAX), 
                menuBgColor NVARCHAR(255), 
                sectionBgColor NVARCHAR(255), 
                menuBorderColor NVARCHAR(255), 
                menuFontColor NVARCHAR(255), 
                menuHoverColor NVARCHAR(255), 
                menuFontFamily NVARCHAR(255), 
                menuFontStyle NVARCHAR(255), 
                menuFontWeight NVARCHAR(255), 
                menuWidth INT, 
                menuOrientation NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C40F17718F45C82 ON icap__website_options (website_id) 
            WHERE website_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE icap__website (
                id INT IDENTITY NOT NULL, 
                creation_date DATETIME2(6) NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F8B87FAB32 ON icap__website (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D41BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D418F45C82 FOREIGN KEY (website_id) 
            REFERENCES icap__website (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D4727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__website_page (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__website_options 
            ADD CONSTRAINT FK_C40F17718F45C82 FOREIGN KEY (website_id) 
            REFERENCES icap__website (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website_page 
            DROP CONSTRAINT FK_FB66D1D4727ACA70
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            DROP CONSTRAINT FK_FB66D1D418F45C82
        ");
        $this->addSql("
            ALTER TABLE icap__website_options 
            DROP CONSTRAINT FK_C40F17718F45C82
        ");
        $this->addSql("
            DROP TABLE icap__website_page
        ");
        $this->addSql("
            DROP TABLE icap__website_options
        ");
        $this->addSql("
            DROP TABLE icap__website
        ");
    }
}