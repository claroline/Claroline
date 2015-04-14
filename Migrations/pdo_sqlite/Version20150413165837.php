<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/13 04:58:39
 */
class Version20150413165837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD COLUMN show_avatar BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD COLUMN show_mail BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD COLUMN show_phone BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_user_information 
            ADD COLUMN show_description BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_user_information AS 
            SELECT id, 
            city, 
            birthDate 
            FROM icap__portfolio_widget_user_information
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_user_information
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_user_information (
                id INTEGER NOT NULL, 
                city VARCHAR(255) DEFAULT NULL, 
                birthDate DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E2BFAA03BF396750 FOREIGN KEY (id) 
                REFERENCES icap__portfolio_abstract_widget (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_user_information (id, city, birthDate) 
            SELECT id, 
            city, 
            birthDate 
            FROM __temp__icap__portfolio_widget_user_information
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_user_information
        ");
    }
}