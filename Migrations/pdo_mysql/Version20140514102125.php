<?php

namespace Icap\PortfolioBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/14 10:21:25
 */
class Version20140514102125 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_users (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                INDEX IDX_3980F8F8A76ED395 (user_id), 
                INDEX IDX_3980F8F8B96B5643 (portfolio_id), 
                UNIQUE INDEX portfolio_users_unique_idx (portfolio_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                title VARCHAR(128) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                visibility INT NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                deletedAt DATETIME DEFAULT NULL, 
                UNIQUE INDEX UNIQ_8B1895D989D9B62 (slug), 
                INDEX IDX_8B1895DA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_type (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_unique TINYINT(1) NOT NULL, 
                is_deletable TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_3E00FC8F5E237E06 (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_node (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_type_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                INDEX IDX_37A143E3CB638B52 (widget_type_id), 
                INDEX IDX_37A143E3B96B5643 (portfolio_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_user_information (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_node_id INT DEFAULT NULL, 
                city VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_E2BFAA0348229816 (widget_node_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            ALTER TABLE icap__portfolio_widget_node 
            ADD CONSTRAINT FK_37A143E3CB638B52 FOREIGN KEY (widget_type_id) 
            REFERENCES icap__portfolio_widget_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            ADD CONSTRAINT FK_37A143E3B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
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
            DROP FOREIGN KEY FK_3980F8F8B96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            DROP FOREIGN KEY FK_37A143E3B96B5643
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_node 
            DROP FOREIGN KEY FK_37A143E3CB638B52
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            DROP FOREIGN KEY FK_E2BFAA0348229816
        ");
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