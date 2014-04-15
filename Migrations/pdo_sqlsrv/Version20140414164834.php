<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                PRIMARY KEY (id)
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
            WHERE portfolio_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                name NVARCHAR(128) NOT NULL, 
                slug NVARCHAR(128) NOT NULL, 
                visibility INT NOT NULL, 
                createdAt DATETIME2(6) NOT NULL, 
                updatedAt DATETIME2(6) NOT NULL, 
                deletedAt DATETIME2(6), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8B1895D989D9B62 ON icap__portfolio (slug) 
            WHERE slug IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_8B1895DA76ED395 ON icap__portfolio (user_id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            ADD CONSTRAINT FK_3980F8F8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            ADD CONSTRAINT FK_3980F8F8B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD CONSTRAINT FK_8B1895DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            DROP CONSTRAINT FK_3980F8F8B96B5643
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_users
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
    }
}