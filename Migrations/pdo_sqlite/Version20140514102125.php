<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/14 10:21:26
 */
class Version20140514102125 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_users (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                portfolio_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3980F8F8A76ED395 ON icap__portfolio_users (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3980F8F8B96B5643 ON icap__portfolio_users (portfolio_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_users_unique_idx ON icap__portfolio_users (portfolio_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                title VARCHAR(128) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                visibility INTEGER NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                deletedAt DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8B1895D989D9B62 ON icap__portfolio (slug)
        ");
        $this->addSql("
            CREATE INDEX IDX_8B1895DA76ED395 ON icap__portfolio (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_type (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_unique BOOLEAN NOT NULL, 
                is_deletable BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3E00FC8F5E237E06 ON icap__portfolio_widget_type (name)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_node (
                id INTEGER NOT NULL, 
                widget_type_id INTEGER NOT NULL, 
                portfolio_id INTEGER NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_37A143E3CB638B52 ON icap__portfolio_widget_node (widget_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_37A143E3B96B5643 ON icap__portfolio_widget_node (portfolio_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_user_information (
                id INTEGER NOT NULL, 
                widget_node_id INTEGER DEFAULT NULL, 
                city VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E2BFAA0348229816 ON icap__portfolio_widget_user_information (widget_node_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_users
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_type
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_node
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_user_information
        ");
    }
}