<?php

namespace Icap\PortfolioBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/06/12 04:10:53
 */
class Version20150612161049 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_widget (
                portfolio_id INT NOT NULL, 
                widget_id INT NOT NULL, 
                col INT DEFAULT 0 NOT NULL, 
                row INT DEFAULT 0 NOT NULL, 
                size_x INT DEFAULT 1 NOT NULL, 
                size_y INT DEFAULT 1 NOT NULL, 
                INDEX IDX_EF643D7FB96B5643 (portfolio_id), 
                INDEX IDX_EF643D7FFBE885E2 (widget_id), 
                PRIMARY KEY(portfolio_id, widget_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget 
            ADD CONSTRAINT FK_EF643D7FB96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget 
            ADD CONSTRAINT FK_EF643D7FFBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP FOREIGN KEY FK_3E7AEFBBB96B5643
        ");
        $this->addSql("
            DROP INDEX IDX_3E7AEFBBB96B5643 ON icap__portfolio_abstract_widget
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP col, 
            DROP row, 
            DROP size_x, 
            DROP size_y, 
            CHANGE portfolio_id user_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT FK_3E7AEFBBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3E7AEFBBA76ED395 ON icap__portfolio_abstract_widget (user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_widget
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP FOREIGN KEY FK_3E7AEFBBA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_3E7AEFBBA76ED395 ON icap__portfolio_abstract_widget
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD col INT DEFAULT 0 NOT NULL, 
            ADD row INT DEFAULT 0 NOT NULL, 
            ADD size_x INT DEFAULT 1 NOT NULL, 
            ADD size_y INT DEFAULT 1 NOT NULL, 
            CHANGE user_id portfolio_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT FK_3E7AEFBBB96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_3E7AEFBBB96B5643 ON icap__portfolio_abstract_widget (portfolio_id)
        ");
    }
}