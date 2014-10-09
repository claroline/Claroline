<?php

namespace Icap\PortfolioBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/09 11:22:28
 */
class Version20141009112225 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio_comments (
                id INT AUTO_INCREMENT NOT NULL, 
                portfolio_id INT NOT NULL, 
                sender_id INT NOT NULL, 
                message LONGTEXT NOT NULL, 
                sending_date DATETIME NOT NULL, 
                INDEX IDX_D4662DE3B96B5643 (portfolio_id), 
                INDEX IDX_D4662DE3F624B39D (sender_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_guides (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                portfolio_id INT NOT NULL, 
                INDEX IDX_27EAB640A76ED395 (user_id), 
                INDEX IDX_27EAB640B96B5643 (portfolio_id), 
                UNIQUE INDEX portfolio_users_unique_idx (portfolio_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges (
                id INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_widget_badges_badge (
                id INT AUTO_INCREMENT NOT NULL, 
                badge_id INT NOT NULL, 
                widget_id INT NOT NULL, 
                INDEX IDX_25D41B98F7A2C2FC (badge_id), 
                INDEX IDX_25D41B98FBE885E2 (widget_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            DROP TABLE icap__portfolio_comments
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_guides
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_widget_badges_badge
        ");
    }
}