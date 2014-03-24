<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/06 09:21:26
 */
class Version20140306092123 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_badge_collection (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                is_shared BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_BB3FD2DDA76ED395 (user_id), 
                UNIQUE INDEX slug_idx (slug)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_badge_collection_badges (
                badgecollection_id INT NOT NULL, 
                badge_id INT NOT NULL, 
                PRIMARY KEY(badgecollection_id, badge_id), 
                INDEX IDX_FD258D74134B8A11 (badgecollection_id), 
                INDEX IDX_FD258D74F7A2C2FC (badge_id)
            )
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
            ALTER TABLE claro_workspace 
            DROP is_public
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD expired_at DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event CHANGE description description TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD is_expiring BOOLEAN DEFAULT 'false' NOT NULL, 
            ADD expire_duration INT DEFAULT NULL, 
            ADD expire_period INT DEFAULT NULL, 
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
            ALTER TABLE claro_event CHANGE description description VARCHAR(255) DEFAULT NULL
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
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD is_public BOOLEAN NOT NULL
        ");
    }
}