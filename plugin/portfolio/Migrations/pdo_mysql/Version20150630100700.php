<?php

namespace Icap\PortfolioBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/06/30 10:07:01
 */
class Version20150630100700 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__portfolio_widget (
                id INT AUTO_INCREMENT NOT NULL, 
                portfolio_id INT NOT NULL, 
                widget_id INT NOT NULL, 
                col INT DEFAULT 0 NOT NULL, 
                row INT DEFAULT 0 NOT NULL, 
                size_x INT DEFAULT 1 NOT NULL, 
                size_y INT DEFAULT 1 NOT NULL, 
                widgetType VARCHAR(255) NOT NULL, 
                INDEX IDX_EF643D7FB96B5643 (portfolio_id), 
                INDEX IDX_EF643D7FFBE885E2 (widget_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_widget 
            ADD CONSTRAINT FK_EF643D7FB96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_widget 
            ADD CONSTRAINT FK_EF643D7FFBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio 
            ADD title VARCHAR(128) NOT NULL, 
            ADD slug VARCHAR(128) DEFAULT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_8B1895D989D9B62 ON icap__portfolio (slug)
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP FOREIGN KEY FK_3E7AEFBBB96B5643
        ');
        $this->addSql('
            DROP INDEX IDX_3E7AEFBBB96B5643 ON icap__portfolio_abstract_widget
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP col, 
            DROP row, 
            DROP size_x, 
            DROP size_y, 
            CHANGE portfolio_id user_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT FK_3E7AEFBBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_3E7AEFBBA76ED395 ON icap__portfolio_abstract_widget (user_id)
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_widget_type 
            DROP is_unique, 
            DROP is_deletable
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__portfolio_widget
        ');
        $this->addSql('
            DROP INDEX UNIQ_8B1895D989D9B62 ON icap__portfolio
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio 
            DROP title, 
            DROP slug
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP FOREIGN KEY FK_3E7AEFBBA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_3E7AEFBBA76ED395 ON icap__portfolio_abstract_widget
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD col INT DEFAULT 0 NOT NULL, 
            ADD row INT DEFAULT 0 NOT NULL, 
            ADD size_x INT DEFAULT 1 NOT NULL, 
            ADD size_y INT DEFAULT 1 NOT NULL, 
            CHANGE user_id portfolio_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD CONSTRAINT FK_3E7AEFBBB96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_3E7AEFBBB96B5643 ON icap__portfolio_abstract_widget (portfolio_id)
        ');
        $this->addSql('
            ALTER TABLE icap__portfolio_widget_type 
            ADD is_unique TINYINT(1) NOT NULL, 
            ADD is_deletable TINYINT(1) NOT NULL
        ');
    }
}
