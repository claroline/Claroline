<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/28 01:07:23
 */
class Version20141028130721 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_teams (
                id INT IDENTITY NOT NULL, 
                team_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_BBC17F49296CD8AE ON icap__portfolio_teams (team_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_BBC17F49B96B5643 ON icap__portfolio_teams (portfolio_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_teams_unique_idx ON icap__portfolio_teams (portfolio_id, team_id) 
            WHERE portfolio_id IS NOT NULL 
            AND team_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_comments (
                id INT IDENTITY NOT NULL, 
                portfolio_id INT NOT NULL, 
                sender_id INT NOT NULL, 
                message VARCHAR(MAX) NOT NULL, 
                sending_date DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                PRIMARY KEY (id)
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
            WHERE portfolio_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_text (
                id INT NOT NULL, 
                text VARCHAR(MAX), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_teams 
            ADD CONSTRAINT FK_BBC17F49296CD8AE FOREIGN KEY (team_id) 
            REFERENCES claro_team (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_teams 
            ADD CONSTRAINT FK_BBC17F49B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_comments 
            ADD CONSTRAINT FK_D4662DE3B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_comments 
            ADD CONSTRAINT FK_D4662DE3F624B39D FOREIGN KEY (sender_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_guides 
            ADD CONSTRAINT FK_27EAB640A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_guides 
            ADD CONSTRAINT FK_27EAB640B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_text 
            ADD CONSTRAINT FK_89550C61BF396750 FOREIGN KEY (id) 
            REFERENCES icap__portfolio_abstract_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD commentsViewAt DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            ADD label NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            DROP CONSTRAINT FK_25D41B98F7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            ADD CONSTRAINT FK_25D41B98F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__portfolio_teams
        ");
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
            ALTER TABLE icap__portfolio 
            DROP COLUMN commentsViewAt
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_abstract_widget 
            DROP COLUMN label
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            DROP CONSTRAINT FK_25D41B98F7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_badges_badge 
            ADD CONSTRAINT FK_25D41B98F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_user_badge (id)
        ");
    }
}