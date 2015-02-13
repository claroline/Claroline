<?php

namespace Icap\PortfolioBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/13 02:26:35
 */
class Version20150213142632 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_experience (
                id INT NOT NULL, 
                post VARCHAR(255) NOT NULL, 
                companyName VARCHAR(255) NOT NULL, 
                startDate DATETIME DEFAULT NULL, 
                endDate DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD CONSTRAINT FK_CD7379A3BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_widget_experience
        ");
    }
}