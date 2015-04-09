<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/09 10:57:35
 */
class Version20150409105732 extends AbstractMigration
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
                col INTEGER DEFAULT 1 NOT NULL, 
                \"row\" INTEGER DEFAULT 1 NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                widget_type VARCHAR(255) NOT NULL, 
                label VARCHAR(255) NOT NULL, 
                size_x INTEGER DEFAULT 1 NOT NULL, 
                size_y INTEGER DEFAULT 1 NOT NULL, 
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
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_formations AS 
            SELECT id, 
            name, 
            startDate, 
            endDate 
            FROM icap__portfolio_widget_formations
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_formations
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_formations (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                startDate DATETIME NOT NULL, 
                endDate DATETIME DEFAULT NULL, 
                establishmentName VARCHAR(255) DEFAULT NULL, 
                diploma VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_88739997BF396750 FOREIGN KEY (id) 
                REFERENCES icap__portfolio_abstract_widget (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_formations (id, name, startDate, endDate) 
            SELECT id, 
            name, 
            startDate, 
            endDate 
            FROM __temp__icap__portfolio_widget_formations
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_formations
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_experience AS 
            SELECT id, 
            post, 
            companyName, 
            startDate, 
            endDate 
            FROM icap__portfolio_widget_experience
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_experience
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_experience (
                id INTEGER NOT NULL, 
                post VARCHAR(255) NOT NULL, 
                companyName VARCHAR(255) NOT NULL, 
                startDate DATETIME NOT NULL, 
                endDate DATETIME DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                website VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CD7379A3BF396750 FOREIGN KEY (id) 
                REFERENCES icap__portfolio_abstract_widget (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_experience (
                id, post, companyName, startDate, endDate
            ) 
            SELECT id, 
            post, 
            companyName, 
            startDate, 
            endDate 
            FROM __temp__icap__portfolio_widget_experience
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_experience
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
                col INTEGER DEFAULT 1 NOT NULL, 
                \"row\" INTEGER DEFAULT 1 NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                widget_type VARCHAR(255) NOT NULL, 
                size_x INTEGER DEFAULT 0 NOT NULL, 
                size_y INTEGER DEFAULT 0 NOT NULL, 
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
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_experience AS 
            SELECT id, 
            post, 
            companyName, 
            startDate, 
            endDate 
            FROM icap__portfolio_widget_experience
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_experience
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_experience (
                id INTEGER NOT NULL, 
                post VARCHAR(255) NOT NULL, 
                companyName VARCHAR(255) NOT NULL, 
                startDate DATETIME DEFAULT NULL, 
                endDate DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_CD7379A3BF396750 FOREIGN KEY (id) 
                REFERENCES icap__portfolio_abstract_widget (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_experience (
                id, post, companyName, startDate, endDate
            ) 
            SELECT id, 
            post, 
            companyName, 
            startDate, 
            endDate 
            FROM __temp__icap__portfolio_widget_experience
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_experience
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_formations AS 
            SELECT id, 
            name, 
            startDate, 
            endDate 
            FROM icap__portfolio_widget_formations
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_formations
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_formations (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                startDate DATETIME DEFAULT NULL, 
                endDate DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_88739997BF396750 FOREIGN KEY (id) 
                REFERENCES icap__portfolio_abstract_widget (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_formations (id, name, startDate, endDate) 
            SELECT id, 
            name, 
            startDate, 
            endDate 
            FROM __temp__icap__portfolio_widget_formations
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_formations
        ");
    }
}