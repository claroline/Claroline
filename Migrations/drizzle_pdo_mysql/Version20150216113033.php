<?php

namespace Icap\PortfolioBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/16 11:30:36
 */
class Version20150216113033 extends AbstractMigration
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
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_experience 
            ADD CONSTRAINT FK_CD7379A3BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            DROP disposition
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD size_x INT DEFAULT 0 NOT NULL, 
            ADD size_y INT DEFAULT 0 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_widget_experience
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD disposition INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP size_x, 
            DROP size_y
        ");
    }
}