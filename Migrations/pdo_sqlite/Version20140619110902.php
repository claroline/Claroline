<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/19 11:09:03
 */
class Version20140619110902 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD COLUMN disposition INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD COLUMN col INTEGER DEFAULT 1 NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD COLUMN \"row\" INTEGER DEFAULT 1 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_8B1895DA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio AS 
            SELECT id, 
            user_id, 
            visibility, 
            deletedAt 
            FROM icap__portfolio
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                visibility INTEGER NOT NULL, 
                deletedAt DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_8B1895DA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio (
                id, user_id, visibility, deletedAt
            ) 
            SELECT id, 
            user_id, 
            visibility, 
            deletedAt 
            FROM __temp__icap__portfolio
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio
        ");
        $this->addSql("
            CREATE INDEX IDX_8B1895DA76ED395 ON icap__portfolio (user_id)
        ");
        $this->addSql("
            DROP INDEX IDX_3E7AEFBBB96B5643
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_abstract_widget AS 
            SELECT id, 
            portfolio_id, 
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
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                widget_type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3E7AEFBBB96B5643 FOREIGN KEY (portfolio_id) 
                REFERENCES icap__portfolio (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_abstract_widget (
                id, portfolio_id, createdAt, updatedAt, 
                widget_type
            ) 
            SELECT id, 
            portfolio_id, 
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