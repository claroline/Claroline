<?php

namespace Icap\WebsiteBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/02 04:30:53
 */
class Version20140902163052 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website_options 
            ADD COLUMN menuFontSize INTEGER DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_C40F17718F45C82
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__website_options AS 
            SELECT id, 
            website_id, 
            copyrightEnabled, 
            copyrightText, 
            analyticsProvider, 
            analyticsAccountId, 
            cssCode, 
            bgColor, 
            bgImage, 
            bgRepeat, 
            bgPosition, 
            bannerBgColor, 
            bannerBgImage, 
            bannerBgRepeat, 
            bannerBgPosition, 
            bannerHeight, 
            bannerEnabled, 
            bannerText, 
            footerBgColor, 
            footerBgImage, 
            footerBgRepeat, 
            footerBgPosition, 
            footerHeight, 
            footerEnabled, 
            footerText, 
            menuBgColor, 
            sectionBgColor, 
            menuBorderColor, 
            menuFontColor, 
            menuHoverColor, 
            menuFontFamily, 
            menuFontStyle, 
            menuFontWeight, 
            menuWidth, 
            menuOrientation 
            FROM icap__website_options
        ");
        $this->addSql("
            DROP TABLE icap__website_options
        ");
        $this->addSql("
            CREATE TABLE icap__website_options (
                id INTEGER NOT NULL, 
                website_id INTEGER DEFAULT NULL, 
                copyrightEnabled BOOLEAN NOT NULL, 
                copyrightText VARCHAR(255) DEFAULT NULL, 
                analyticsProvider VARCHAR(255) DEFAULT NULL, 
                analyticsAccountId VARCHAR(255) DEFAULT NULL, 
                cssCode CLOB DEFAULT NULL, 
                bgColor VARCHAR(255) DEFAULT NULL, 
                bgImage VARCHAR(255) DEFAULT NULL, 
                bgRepeat VARCHAR(255) DEFAULT NULL, 
                bgPosition VARCHAR(255) DEFAULT NULL, 
                bannerBgColor VARCHAR(255) DEFAULT NULL, 
                bannerBgImage VARCHAR(255) DEFAULT NULL, 
                bannerBgRepeat VARCHAR(255) DEFAULT NULL, 
                bannerBgPosition VARCHAR(255) DEFAULT NULL, 
                bannerHeight INTEGER DEFAULT NULL, 
                bannerEnabled BOOLEAN NOT NULL, 
                bannerText CLOB DEFAULT NULL, 
                footerBgColor VARCHAR(255) DEFAULT NULL, 
                footerBgImage VARCHAR(255) DEFAULT NULL, 
                footerBgRepeat VARCHAR(255) DEFAULT NULL, 
                footerBgPosition VARCHAR(255) DEFAULT NULL, 
                footerHeight INTEGER DEFAULT NULL, 
                footerEnabled BOOLEAN NOT NULL, 
                footerText CLOB DEFAULT NULL, 
                menuBgColor VARCHAR(255) DEFAULT NULL, 
                sectionBgColor VARCHAR(255) DEFAULT NULL, 
                menuBorderColor VARCHAR(255) DEFAULT NULL, 
                menuFontColor VARCHAR(255) DEFAULT NULL, 
                menuHoverColor VARCHAR(255) DEFAULT NULL, 
                menuFontFamily VARCHAR(255) DEFAULT NULL, 
                menuFontStyle VARCHAR(255) DEFAULT NULL, 
                menuFontWeight VARCHAR(255) DEFAULT NULL, 
                menuWidth INTEGER DEFAULT NULL, 
                menuOrientation VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_C40F17718F45C82 FOREIGN KEY (website_id) 
                REFERENCES icap__website (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__website_options (
                id, website_id, copyrightEnabled, 
                copyrightText, analyticsProvider, 
                analyticsAccountId, cssCode, bgColor, 
                bgImage, bgRepeat, bgPosition, bannerBgColor, 
                bannerBgImage, bannerBgRepeat, bannerBgPosition, 
                bannerHeight, bannerEnabled, bannerText, 
                footerBgColor, footerBgImage, footerBgRepeat, 
                footerBgPosition, footerHeight, 
                footerEnabled, footerText, menuBgColor, 
                sectionBgColor, menuBorderColor, 
                menuFontColor, menuHoverColor, menuFontFamily, 
                menuFontStyle, menuFontWeight, menuWidth, 
                menuOrientation
            ) 
            SELECT id, 
            website_id, 
            copyrightEnabled, 
            copyrightText, 
            analyticsProvider, 
            analyticsAccountId, 
            cssCode, 
            bgColor, 
            bgImage, 
            bgRepeat, 
            bgPosition, 
            bannerBgColor, 
            bannerBgImage, 
            bannerBgRepeat, 
            bannerBgPosition, 
            bannerHeight, 
            bannerEnabled, 
            bannerText, 
            footerBgColor, 
            footerBgImage, 
            footerBgRepeat, 
            footerBgPosition, 
            footerHeight, 
            footerEnabled, 
            footerText, 
            menuBgColor, 
            sectionBgColor, 
            menuBorderColor, 
            menuFontColor, 
            menuHoverColor, 
            menuFontFamily, 
            menuFontStyle, 
            menuFontWeight, 
            menuWidth, 
            menuOrientation 
            FROM __temp__icap__website_options
        ");
        $this->addSql("
            DROP TABLE __temp__icap__website_options
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C40F17718F45C82 ON icap__website_options (website_id)
        ");
    }
}