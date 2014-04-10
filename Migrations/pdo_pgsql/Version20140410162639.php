<?php

namespace Icap\PortfolioBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/10 04:26:42
 */
class Version20140410162639 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio (
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                name VARCHAR(128) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                share_policy INT NOT NULL, 
                createdAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                updatedAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                deletedAt TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
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
            CREATE TABLE icap__portfolio_shared_users (
                portfolio_id INT NOT NULL, 
                user_id INT NOT NULL, 
                PRIMARY KEY(portfolio_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8EACC994B96B5643 ON icap__portfolio_shared_users (portfolio_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8EACC994A76ED395 ON icap__portfolio_shared_users (user_id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD CONSTRAINT FK_8B1895DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_shared_users 
            ADD CONSTRAINT FK_8EACC994B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_shared_users 
            ADD CONSTRAINT FK_8EACC994A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_shared_users 
            DROP CONSTRAINT FK_8EACC994B96B5643
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_shared_users
        ");
    }
}