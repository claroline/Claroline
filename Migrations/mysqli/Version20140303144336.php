<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/03 02:43:40
 */
class Version20140303144336 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_badge_collection (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                is_shared TINYINT(1) NOT NULL, 
                INDEX IDX_BB3FD2DDA76ED395 (user_id), 
                UNIQUE INDEX slug_idx (slug), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_badge_collection_badges (
                badgecollection_id INT NOT NULL, 
                badge_id INT NOT NULL, 
                INDEX IDX_FD258D74134B8A11 (badgecollection_id), 
                INDEX IDX_FD258D74F7A2C2FC (badge_id), 
                PRIMARY KEY(badgecollection_id, badge_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_badge_collection 
            ADD CONSTRAINT FK_BB3FD2DDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_collection_badges 
            ADD CONSTRAINT FK_FD258D74134B8A11 FOREIGN KEY (badgecollection_id) 
            REFERENCES claro_badge_collection (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_collection_badges 
            ADD CONSTRAINT FK_FD258D74F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP FOREIGN KEY FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD expired_at DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD is_expiring TINYINT(1) DEFAULT '0' NOT NULL, 
            ADD expire_duration INT DEFAULT NULL, 
            ADD expire_period SMALLINT DEFAULT NULL, 
            DROP expired_at
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_collection_badges 
            DROP FOREIGN KEY FK_FD258D74134B8A11
        ");
        $this->addSql("
            DROP TABLE claro_badge_collection
        ");
        $this->addSql("
            DROP TABLE claro_badge_collection_badges
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD expired_at DATETIME DEFAULT NULL, 
            DROP is_expiring, 
            DROP expire_duration, 
            DROP expire_period
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP FOREIGN KEY FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            DROP expired_at
        ");
    }
}