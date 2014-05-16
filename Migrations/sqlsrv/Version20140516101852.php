<?php

namespace Icap\PortfolioBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/16 10:18:54
 */
class Version20140516101852 extends AbstractMigration
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
                visibility INT NOT NULL, 
                deletedAt DATETIME2(6), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8B1895DA76ED395 ON icap__portfolio (user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_title (
                id INT IDENTITY NOT NULL, 
                widget_node_id INT, 
                title NVARCHAR(128) NOT NULL, 
                slug NVARCHAR(128) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1431A01D989D9B62 ON icap__portfolio_widget_title (slug) 
            WHERE slug IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_1431A01D48229816 ON icap__portfolio_widget_title (widget_node_id) 
            WHERE widget_node_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_type (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                is_unique BIT NOT NULL, 
                is_deletable BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3E00FC8F5E237E06 ON icap__portfolio_widget_type (name) 
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_node (
                id INT IDENTITY NOT NULL, 
                widget_type_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                createdAt DATETIME2(6) NOT NULL, 
                updatedAt DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                widget_node_id INT, 
                city NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E2BFAA0348229816 ON icap__portfolio_widget_user_information (widget_node_id) 
            WHERE widget_node_id IS NOT NULL
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
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_title 
            ADD CONSTRAINT FK_1431A01D48229816 FOREIGN KEY (widget_node_id) 
            REFERENCES icap__portfolio_widget_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            ADD CONSTRAINT FK_37A143E3CB638B52 FOREIGN KEY (widget_type_id) 
            REFERENCES icap__portfolio_widget_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            ADD CONSTRAINT FK_37A143E3B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD CONSTRAINT FK_E2BFAA0348229816 FOREIGN KEY (widget_node_id) 
            REFERENCES icap__portfolio_widget_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_users 
            DROP CONSTRAINT FK_3980F8F8B96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            DROP CONSTRAINT FK_37A143E3B96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            DROP CONSTRAINT FK_37A143E3CB638B52
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_title 
            DROP CONSTRAINT FK_1431A01D48229816
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP CONSTRAINT FK_E2BFAA0348229816
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_users
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_title
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