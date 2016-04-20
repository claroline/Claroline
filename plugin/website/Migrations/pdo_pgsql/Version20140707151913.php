<?php

namespace Icap\WebsiteBundle\Migrations\pdo_pgsql;

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
                id SERIAL NOT NULL, 
                resource_node_id INT DEFAULT NULL, 
                website_id INT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                visible BOOLEAN NOT NULL, 
                creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                richText TEXT DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                isSection BOOLEAN NOT NULL, 
                description VARCHAR(255) DEFAULT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT DEFAULT NULL, 
                PRIMARY KEY(id)
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
                id SERIAL NOT NULL, 
                website_id INT DEFAULT NULL, 
                copyrightEnabled BOOLEAN NOT NULL, 
                copyrightText VARCHAR(255) DEFAULT NULL, 
                analyticsProvider VARCHAR(255) DEFAULT NULL, 
                analyticsAccountId VARCHAR(255) DEFAULT NULL, 
                cssCode TEXT DEFAULT NULL, 
                bgColor VARCHAR(255) DEFAULT NULL, 
                bgImage VARCHAR(255) DEFAULT NULL, 
                bgRepeat VARCHAR(255) DEFAULT NULL, 
                bgPosition VARCHAR(255) DEFAULT NULL, 
                bannerBgColor VARCHAR(255) DEFAULT NULL, 
                bannerBgImage VARCHAR(255) DEFAULT NULL, 
                bannerBgRepeat VARCHAR(255) DEFAULT NULL, 
                bannerBgPosition VARCHAR(255) DEFAULT NULL, 
                bannerHeight INT DEFAULT NULL, 
                bannerEnabled BOOLEAN NOT NULL, 
                bannerText TEXT DEFAULT NULL, 
                footerBgColor VARCHAR(255) DEFAULT NULL, 
                footerBgImage VARCHAR(255) DEFAULT NULL, 
                footerBgRepeat VARCHAR(255) DEFAULT NULL, 
                footerBgPosition VARCHAR(255) DEFAULT NULL, 
                footerHeight INT DEFAULT NULL, 
                footerEnabled BOOLEAN NOT NULL, 
                footerText TEXT DEFAULT NULL, 
                menuBgColor VARCHAR(255) DEFAULT NULL, 
                sectionBgColor VARCHAR(255) DEFAULT NULL, 
                menuBorderColor VARCHAR(255) DEFAULT NULL, 
                menuFontColor VARCHAR(255) DEFAULT NULL, 
                menuHoverColor VARCHAR(255) DEFAULT NULL, 
                menuFontFamily VARCHAR(255) DEFAULT NULL, 
                menuFontStyle VARCHAR(255) DEFAULT NULL, 
                menuFontWeight VARCHAR(255) DEFAULT NULL, 
                menuWidth INT DEFAULT NULL, 
                menuOrientation VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C40F17718F45C82 ON icap__website_options (website_id)
        ");
        $this->addSql("
            CREATE TABLE icap__website (
                id SERIAL NOT NULL, 
                creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_452309F8B87FAB32 ON icap__website (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D41BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D418F45C82 FOREIGN KEY (website_id) 
            REFERENCES icap__website (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD CONSTRAINT FK_FB66D1D4727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__website_page (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__website_options 
            ADD CONSTRAINT FK_C40F17718F45C82 FOREIGN KEY (website_id) 
            REFERENCES icap__website (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__website 
            ADD CONSTRAINT FK_452309F8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
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