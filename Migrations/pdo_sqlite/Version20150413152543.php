<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/13 03:25:45
 */
class Version20150413152543 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_3E7AEFBBB96B5643
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_abstract_widget AS 
            SELECT id, 
            portfolio_id, 
            col, 
            \"row\", 
            createdAt, 
            updatedAt, 
            widget_type, 
            label, 
            size_x, 
            size_y 
            FROM icap__portfolio_abstract_widget
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_abstract_widget
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_abstract_widget (
                id INTEGER NOT NULL, 
                portfolio_id INTEGER NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                widget_type VARCHAR(255) NOT NULL, 
                label VARCHAR(255) NOT NULL, 
                size_x INTEGER DEFAULT 1 NOT NULL, 
                size_y INTEGER DEFAULT 1 NOT NULL, 
                col INTEGER DEFAULT 0 NOT NULL, 
                \"row\" INTEGER DEFAULT 0 NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3E7AEFBBB96B5643 FOREIGN KEY (portfolio_id) 
                REFERENCES icap__portfolio (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_abstract_widget (
                id, portfolio_id, col, \"row\", createdAt, 
                updatedAt, widget_type, label, size_x, 
                size_y
            ) 
            SELECT id, 
            portfolio_id, 
            col, 
            \"row\", 
            createdAt, 
            updatedAt, 
            widget_type, 
            label, 
            size_x, 
            size_y 
            FROM __temp__icap__portfolio_abstract_widget
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_abstract_widget
        ");
        $this->addSql("
            CREATE INDEX IDX_3E7AEFBBB96B5643 ON icap__portfolio_abstract_widget (portfolio_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_3E7AEFBBB96B5643
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_abstract_widget AS 
            SELECT id, 
            portfolio_id, 
            label, 
            col, 
            \"row\", 
            size_x, 
            size_y, 
            createdAt, 
            updatedAt, 
            widget_type 
            FROM icap__portfolio_abstract_widget
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_abstract_widget
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_abstract_widget (
                id INTEGER NOT NULL, 
                portfolio_id INTEGER NOT NULL, 
                label VARCHAR(255) NOT NULL, 
                size_x INTEGER DEFAULT 1 NOT NULL, 
                size_y INTEGER DEFAULT 1 NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                widget_type VARCHAR(255) NOT NULL, 
                col INTEGER DEFAULT 1 NOT NULL, 
                \"row\" INTEGER DEFAULT 1 NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3E7AEFBBB96B5643 FOREIGN KEY (portfolio_id) 
                REFERENCES icap__portfolio (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_abstract_widget (
                id, portfolio_id, label, col, \"row\", 
                size_x, size_y, createdAt, updatedAt, 
                widget_type
            ) 
            SELECT id, 
            portfolio_id, 
            label, 
            col, 
            \"row\", 
            size_x, 
            size_y, 
            createdAt, 
            updatedAt, 
            widget_type 
            FROM __temp__icap__portfolio_abstract_widget
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_abstract_widget
        ");
        $this->addSql("
            CREATE INDEX IDX_3E7AEFBBB96B5643 ON icap__portfolio_abstract_widget (portfolio_id)
        ");
    }
}