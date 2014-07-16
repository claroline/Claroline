<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/16 11:09:12
 */
class Version20140716110911 extends AbstractMigration
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
                visibility INTEGER NOT NULL, 
                disposition INTEGER NOT NULL, 
                deletedAt DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8B1895DA76ED395 ON icap__portfolio (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_groups (
                id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                portfolio_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_9AF01ADFFE54D947 ON icap__portfolio_groups (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_9AF01ADFB96B5643 ON icap__portfolio_groups (portfolio_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_groups_unique_idx ON icap__portfolio_groups (portfolio_id, group_id)
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
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3E7AEFBBB96B5643 ON icap__portfolio_abstract_widget (portfolio_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_formations (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                startDate DATETIME DEFAULT NULL, 
                endDate DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_title (
                id INTEGER NOT NULL, 
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
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_unique BOOLEAN NOT NULL, 
                is_deletable BOOLEAN NOT NULL, 
                icon VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3E00FC8F5E237E06 ON icap__portfolio_widget_type (name)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_formations_resource (
                id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                widget_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_23096D5889329D25 ON icap__portfolio_widget_formations_resource (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_23096D58FBE885E2 ON icap__portfolio_widget_formations_resource (widget_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_skills_skill (
                id INTEGER NOT NULL, 
                widget_id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_98EF40A3FBE885E2 ON icap__portfolio_widget_skills_skill (widget_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_user_information (
                id INTEGER NOT NULL, 
                city VARCHAR(255) DEFAULT NULL, 
                birthDate DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_skills (
                id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_presentation (
                id INTEGER NOT NULL, 
                presentation CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
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
            DROP TABLE icap__portfolio_groups
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_abstract_widget
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_formations
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_title
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_type
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_formations_resource
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_skills_skill
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_user_information
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_skills
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_presentation
        ");
    }
}