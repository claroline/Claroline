<?php

namespace Icap\WebsiteBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/09 04:19:31
 */
class Version20141209161930 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__website_page 
            ADD COLUMN isHomepage BOOLEAN DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_FB66D1D41BAD783F
        ");
        $this->addSql("
            DROP INDEX IDX_FB66D1D418F45C82
        ");
        $this->addSql("
            DROP INDEX IDX_FB66D1D4727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__website_page AS 
            SELECT id, 
            resource_node_id, 
            website_id, 
            parent_id, 
            visible, 
            creation_date, 
            title, 
            richText, 
            url, 
            isSection, 
            description, 
            type, 
            resourceNodeType, 
            lft, 
            lvl, 
            rgt, 
            root 
            FROM icap__website_page
        ");
        $this->addSql("
            DROP TABLE icap__website_page
        ");
        $this->addSql("
            CREATE TABLE icap__website_page (
                id INTEGER NOT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                website_id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                visible BOOLEAN NOT NULL, 
                creation_date DATETIME NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                richText CLOB DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                isSection BOOLEAN NOT NULL, 
                description VARCHAR(255) DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                resourceNodeType VARCHAR(255) DEFAULT NULL, 
                lft INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                root INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_FB66D1D41BAD783F FOREIGN KEY (resource_node_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_FB66D1D418F45C82 FOREIGN KEY (website_id) 
                REFERENCES icap__website (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_FB66D1D4727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES icap__website_page (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__website_page (
                id, resource_node_id, website_id, 
                parent_id, visible, creation_date, 
                title, richText, url, isSection, description, 
                type, resourceNodeType, lft, lvl, 
                rgt, root
            ) 
            SELECT id, 
            resource_node_id, 
            website_id, 
            parent_id, 
            visible, 
            creation_date, 
            title, 
            richText, 
            url, 
            isSection, 
            description, 
            type, 
            resourceNodeType, 
            lft, 
            lvl, 
            rgt, 
            root 
            FROM __temp__icap__website_page
        ");
        $this->addSql("
            DROP TABLE __temp__icap__website_page
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
    }
}