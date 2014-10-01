<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/30 05:39:43
 */
class Version20140930173941 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_evaluators (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                portfolio_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CD4F54F9A76ED395 ON icap__portfolio_evaluators (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CD4F54F9B96B5643 ON icap__portfolio_evaluators (portfolio_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_users_unique_idx ON icap__portfolio_evaluators (portfolio_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges (
                id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges_badge (
                id INTEGER NOT NULL, 
                badge_id INTEGER NOT NULL, 
                widget_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_25D41B98F7A2C2FC ON icap__portfolio_widget_badges_badge (badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_25D41B98FBE885E2 ON icap__portfolio_widget_badges_badge (widget_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_evaluators
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges_badge
        ");
    }
}