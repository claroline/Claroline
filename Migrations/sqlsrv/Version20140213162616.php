<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/13 04:26:21
 */
class Version20140213162616 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_badge_collection (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                name NVARCHAR(255) NOT NULL, 
                is_public BIT NOT NULL, 
                public_id NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_BB3FD2DDA76ED395 ON claro_badge_collection (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_badge_collection_badges (
                badgecollection_id INT NOT NULL, 
                badge_id INT NOT NULL, 
                PRIMARY KEY (badgecollection_id, badge_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FD258D74134B8A11 ON claro_badge_collection_badges (badgecollection_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FD258D74F7A2C2FC ON claro_badge_collection_badges (badge_id)
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
            DROP CONSTRAINT FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_collection_badges 
            DROP CONSTRAINT FK_FD258D74134B8A11
        ");
        $this->addSql("
            DROP TABLE claro_badge_collection
        ");
        $this->addSql("
            DROP TABLE claro_badge_collection_badges
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP CONSTRAINT FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
    }
}