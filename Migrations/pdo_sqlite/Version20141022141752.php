<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/22 02:17:53
 */
class Version20141022141752 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_comments (
                id INTEGER NOT NULL, 
                portfolio_id INTEGER NOT NULL, 
                sender_id INTEGER NOT NULL, 
                message CLOB NOT NULL, 
                sending_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D4662DE3B96B5643 ON icap__portfolio_comments (portfolio_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4662DE3F624B39D ON icap__portfolio_comments (sender_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_guides (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                portfolio_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_27EAB640A76ED395 ON icap__portfolio_guides (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_27EAB640B96B5643 ON icap__portfolio_guides (portfolio_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_users_unique_idx ON icap__portfolio_guides (portfolio_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_text (
                id INTEGER NOT NULL, 
                text CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD COLUMN label VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_25D41B98F7A2C2FC
        ");
        $this->addSql("
            DROP INDEX IDX_25D41B98FBE885E2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_badges_badge AS 
            SELECT id, 
            badge_id, 
            widget_id 
            FROM icap__portfolio_widget_badges_badge
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges_badge
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges_badge (
                id INTEGER NOT NULL, 
                badge_id INTEGER NOT NULL, 
                widget_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_25D41B98F7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_25D41B98FBE885E2 FOREIGN KEY (widget_id) 
                REFERENCES icap__portfolio_widget_badges (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_badges_badge (id, badge_id, widget_id) 
            SELECT id, 
            badge_id, 
            widget_id 
            FROM __temp__icap__portfolio_widget_badges_badge
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_badges_badge
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
            DROP TABLE icap__portfolio_comments
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_guides
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_text
        ");
        $this->addSql("
            DROP INDEX IDX_3E7AEFBBB96B5643
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_abstract_widget AS 
            SELECT id, 
            portfolio_id, 
            col, 
            \"row\", 
            createdAt, 
            updatedAt, 
            widget_type 
            FROM icap__portfolio_abstract_widget
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_abstract_widget
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_abstract_widget (
                id INTEGER NOT NULL, 
                portfolio_id INTEGER NOT NULL, 
                col INTEGER DEFAULT 1 NOT NULL, 
                \"row\" INTEGER DEFAULT 1 NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                widget_type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3E7AEFBBB96B5643 FOREIGN KEY (portfolio_id) 
                REFERENCES icap__portfolio (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_abstract_widget (
                id, portfolio_id, col, \"row\", createdAt, 
                updatedAt, widget_type
            ) 
            SELECT id, 
            portfolio_id, 
            col, 
            \"row\", 
            createdAt, 
            updatedAt, 
            widget_type 
            FROM __temp__icap__portfolio_abstract_widget
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_abstract_widget
        ");
        $this->addSql("
            CREATE INDEX IDX_3E7AEFBBB96B5643 ON icap__portfolio_abstract_widget (portfolio_id)
        ");
        $this->addSql("
            DROP INDEX IDX_25D41B98F7A2C2FC
        ");
        $this->addSql("
            DROP INDEX IDX_25D41B98FBE885E2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__portfolio_widget_badges_badge AS 
            SELECT id, 
            badge_id, 
            widget_id 
            FROM icap__portfolio_widget_badges_badge
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges_badge
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges_badge (
                id INTEGER NOT NULL, 
                badge_id INTEGER NOT NULL, 
                widget_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_25D41B98F7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_user_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_25D41B98FBE885E2 FOREIGN KEY (widget_id) 
                REFERENCES icap__portfolio_widget_badges (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__portfolio_widget_badges_badge (id, badge_id, widget_id) 
            SELECT id, 
            badge_id, 
            widget_id 
            FROM __temp__icap__portfolio_widget_badges_badge
        ");
        $this->addSql("
            DROP TABLE __temp__icap__portfolio_widget_badges_badge
        ");
        $this->addSql("
            CREATE INDEX IDX_25D41B98F7A2C2FC ON icap__portfolio_widget_badges_badge (badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_25D41B98FBE885E2 ON icap__portfolio_widget_badges_badge (widget_id)
        ");
    }
}