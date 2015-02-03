<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/03 10:56:14
 */
class Version20150203105612 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_1431A01D989D9B62
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_title AS 
            SELECT id, 
            title, 
            slug 
            FROM icap__portfolio_widget_title
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_title
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_title (
                id INTEGER NOT NULL, 
                title VARCHAR(128) NOT NULL COLLATE utf8_unicode_ci, 
                slug VARCHAR(128) NOT NULL COLLATE utf8_unicode_ci, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1431A01DBF396750 FOREIGN KEY (id) 
                REFERENCES icap__portfolio_abstract_widget (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_title (id, title, slug) 
            SELECT id, 
            title, 
            slug 
            FROM __temp__icap__portfolio_widget_title
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_title
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1431A01D989D9B62 ON icap__portfolio_widget_title (slug)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_slug_unique_idx ON icap__portfolio_widget_title (slug)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            ADD COLUMN uri VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            ADD COLUMN uriLabel VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_23096D5889329D25
        ");
        $this->addSql("
            DROP INDEX IDX_23096D58FBE885E2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_formations_resource AS 
            SELECT id, 
            resource_id, 
            widget_id 
            FROM icap__portfolio_widget_formations_resource
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_formations_resource
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_formations_resource (
                id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                widget_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_23096D5889329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_23096D58FBE885E2 FOREIGN KEY (widget_id) 
                REFERENCES icap__portfolio_widget_formations (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_formations_resource (id, resource_id, widget_id) 
            SELECT id, 
            resource_id, 
            widget_id 
            FROM __temp__icap__portfolio_widget_formations_resource
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_formations_resource
        ");
        $this->addSql("
            CREATE INDEX IDX_23096D5889329D25 ON icap__portfolio_widget_formations_resource (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_23096D58FBE885E2 ON icap__portfolio_widget_formations_resource (widget_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_1431A01D989D9B62
        ");
        $this->addSql("
            DROP INDEX portfolio_slug_unique_idx
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_title AS 
            SELECT id, 
            title, 
            slug 
            FROM icap__portfolio_widget_title
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_title
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_title (
                id INTEGER NOT NULL, 
                title VARCHAR(128) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1431A01DBF396750 FOREIGN KEY (id) 
                REFERENCES icap__portfolio_abstract_widget (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_title (id, title, slug) 
            SELECT id, 
            title, 
            slug 
            FROM __temp__icap__portfolio_widget_title
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_title
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1431A01D989D9B62 ON icap__portfolio_widget_title (slug)
        ");
    }
}