<?php

namespace Icap\PortfolioBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/19 02:30:46
 */
class Version20140519143045 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_users (
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
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
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                visibility INT NOT NULL, 
                deletedAt TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8B1895DA76ED395 ON icap__portfolio (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_abstract_widget (
                id SERIAL NOT NULL, 
                portfolio_id INT NOT NULL, 
                createdAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                updatedAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                widget_type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3E7AEFBBB96B5643 ON icap__portfolio_abstract_widget (portfolio_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_title (
                id INT NOT NULL, 
                title VARCHAR(128) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1431A01D989D9B62 ON icap__portfolio_widget_title (slug)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_type (
                id SERIAL NOT NULL, 
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
            CREATE TABLE icap__portfolio_widget_user_information (
                id INT NOT NULL, 
                city VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            ADD CONSTRAINT FK_3980F8F8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            ADD CONSTRAINT FK_3980F8F8B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD CONSTRAINT FK_8B1895DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT FK_3E7AEFBBB96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_title 
            ADD CONSTRAINT FK_1431A01DBF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD CONSTRAINT FK_E2BFAA03BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            DROP CONSTRAINT FK_3980F8F8B96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP CONSTRAINT FK_3E7AEFBBB96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_title 
            DROP CONSTRAINT FK_1431A01DBF396750
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP CONSTRAINT FK_E2BFAA03BF396750
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_users
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_abstract_widget
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_title
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_type
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_user_information
        ");
    }
}