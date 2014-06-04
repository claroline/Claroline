<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/25 04:12:02
 */
class Version20140425161201 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD COLUMN comment CLOB DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
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
            issued_at, 
            expired_at 
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
                expired_at DATETIME DEFAULT NULL, 
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
                id, user_id, badge_id, issuer_id, issued_at, 
                expired_at
            ) 
            SELECT id, 
            user_id, 
            badge_id, 
            issuer_id, 
            issued_at, 
            expired_at 
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