<?php

namespace Icap\PortfolioBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/09/30 05:39:44
 */
class Version20140930173941 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_evaluators (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_CD4F54F9A76ED395 (user_id), 
                INDEX IDX_CD4F54F9B96B5643 (portfolio_id), 
                UNIQUE INDEX portfolio_users_unique_idx (portfolio_id, user_id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges (
                id INT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges_badge (
                id INT AUTO_INCREMENT NOT NULL, 
                badge_id INT NOT NULL, 
                widget_id INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_25D41B98F7A2C2FC (badge_id), 
                INDEX IDX_25D41B98FBE885E2 (widget_id)
            )
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_evaluators 
            ADD CONSTRAINT FK_CD4F54F9A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_evaluators 
            ADD CONSTRAINT FK_CD4F54F9B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id)
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
            REFERENCES claro_badge (id)
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
            DROP FOREIGN KEY FK_25D41B98FBE885E2
        ");
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