<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/27 02:58:52
 */
class Version20140227145848 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD COLUMN expired_at DATETIME DEFAULT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_74F39F0F82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_badge AS 
            SELECT id, 
            workspace_id, 
            version, 
            image, 
            automatic_award, 
            deletedAt 
            FROM claro_badge
        ");
        $this->addSql("
            DROP TABLE claro_badge
        ");
        $this->addSql("
            CREATE TABLE claro_badge (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                version INTEGER NOT NULL, 
                image VARCHAR(255) NOT NULL, 
                automatic_award BOOLEAN DEFAULT NULL, 
                deletedAt DATETIME DEFAULT NULL, 
                is_expiring BOOLEAN DEFAULT '0' NOT NULL, 
                expire_duration INTEGER DEFAULT NULL, 
                expire_period INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_74F39F0F82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge (
                id, workspace_id, version, image, automatic_award, 
                deletedAt
            ) 
            SELECT id, 
            workspace_id, 
            version, 
            image, 
            automatic_award, 
            deletedAt 
            FROM __temp__claro_badge
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge
        ");
        $this->addSql("
            CREATE INDEX IDX_74F39F0F82D40A1F ON claro_badge (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_74F39F0F82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_badge AS 
            SELECT id, 
            workspace_id, 
            version, 
            automatic_award, 
            image, 
            deletedAt 
            FROM claro_badge
        ");
        $this->addSql("
            DROP TABLE claro_badge
        ");
        $this->addSql("
            CREATE TABLE claro_badge (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                version INTEGER NOT NULL, 
                automatic_award BOOLEAN DEFAULT NULL, 
                image VARCHAR(255) NOT NULL, 
                deletedAt DATETIME DEFAULT NULL, 
                expired_at DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_74F39F0F82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge (
                id, workspace_id, version, automatic_award, 
                image, deletedAt
            ) 
            SELECT id, 
            workspace_id, 
            version, 
            automatic_award, 
            image, 
            deletedAt 
            FROM __temp__claro_badge
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge
        ");
        $this->addSql("
            CREATE INDEX IDX_74F39F0F82D40A1F ON claro_badge (workspace_id)
        ");
        $this->addSql("
            DROP INDEX IDX_7EBB381FA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_7EBB381FF7A2C2FC
        ");
        $this->addSql("
            DROP INDEX IDX_7EBB381FBB9D6FEE
        ");
        $this->addSql("
            DROP INDEX user_badge_unique
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_badge AS 
            SELECT id, 
            user_id, 
            badge_id, 
            issuer_id, 
            issued_at 
            FROM claro_user_badge
        ");
        $this->addSql("
            DROP TABLE claro_user_badge
        ");
        $this->addSql("
            CREATE TABLE claro_user_badge (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                badge_id INTEGER NOT NULL, 
                issuer_id INTEGER DEFAULT NULL, 
                issued_at DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_7EBB381FA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_7EBB381FF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_7EBB381FBB9D6FEE FOREIGN KEY (issuer_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_badge (
                id, user_id, badge_id, issuer_id, issued_at
            ) 
            SELECT id, 
            user_id, 
            badge_id, 
            issuer_id, 
            issued_at 
            FROM __temp__claro_user_badge
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_badge
        ");
        $this->addSql("
            CREATE INDEX IDX_7EBB381FA76ED395 ON claro_user_badge (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7EBB381FF7A2C2FC ON claro_user_badge (badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7EBB381FBB9D6FEE ON claro_user_badge (issuer_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_badge_unique ON claro_user_badge (user_id, badge_id)
        ");
    }
}