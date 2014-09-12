<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/11 04:18:33
 */
class Version20140911161831 extends AbstractMigration
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
            CREATE TABLE icap__portfolio_widget_badges_user_badge (
                id INT IDENTITY NOT NULL, 
                user_badge_id INT NOT NULL, 
                widget_id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_E104DE03172F26FC ON icap__portfolio_widget_badges_user_badge (user_badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E104DE03FBE885E2 ON icap__portfolio_widget_badges_user_badge (widget_id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges 
            ADD CONSTRAINT FK_C1AF804BBF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_user_badge 
            ADD CONSTRAINT FK_E104DE03172F26FC FOREIGN KEY (user_badge_id) 
            REFERENCES claro_user_badge (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_user_badge 
            ADD CONSTRAINT FK_E104DE03FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES icap__portfolio_widget_badges (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_user_badge 
            DROP CONSTRAINT FK_E104DE03FBE885E2
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges_user_badge
        ");
    }
}