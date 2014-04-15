<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/14 04:48:36
 */
class Version20140414164834 extends AbstractMigration
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
                name VARCHAR(128) NOT NULL, 
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_users
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
    }
}