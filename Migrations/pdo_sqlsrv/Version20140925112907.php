<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/25 11:29:08
 */
class Version20140925112907 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges (
                id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges_badge (
                id INT IDENTITY NOT NULL, 
                badge_id INT NOT NULL, 
                widget_id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_25D41B98F7A2C2FC ON icap__portfolio_widget_badges_badge (badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_25D41B98FBE885E2 ON icap__portfolio_widget_badges_badge (widget_id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges 
            ADD CONSTRAINT FK_C1AF804BBF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            ADD CONSTRAINT FK_25D41B98F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_user_badge (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            ADD CONSTRAINT FK_25D41B98FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES icap__portfolio_widget_badges (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            DROP CONSTRAINT FK_25D41B98FBE885E2
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges_badge
        ");
    }
}