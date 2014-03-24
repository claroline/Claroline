<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                name NVARCHAR(255) NOT NULL, 
                slug NVARCHAR(128) NOT NULL, 
                is_shared BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_BB3FD2DDA76ED395 ON claro_badge_collection (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX slug_idx ON claro_badge_collection (slug) 
            WHERE slug IS NOT NULL
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
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN is_public
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD expired_at DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_event ALTER COLUMN description VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD is_expiring BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD CONSTRAINT DF_74F39F0F_869FAB69 DEFAULT '0' FOR is_expiring
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD expire_duration INT
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD expire_period SMALLINT
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP COLUMN expired_at
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
            ALTER TABLE claro_badge 
            ADD expired_at DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP COLUMN is_expiring
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP COLUMN expire_duration
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP COLUMN expire_period
        ");
        $this->addSql("
            ALTER TABLE claro_event ALTER COLUMN description NVARCHAR(255)
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
        $this->addSql("
            ALTER TABLE claro_user_badge 
            DROP COLUMN expired_at
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD is_public BIT NOT NULL
        ");
    }
}