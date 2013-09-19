<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/19 10:59:18
 */
class Version20130919105916 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id INTEGER NOT NULL, 
                badge_id INTEGER NOT NULL, 
                occurrence INTEGER NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                period VARCHAR(255) NOT NULL, 
                periodDuration INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD COLUMN automatic_award BOOLEAN DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_badge AS 
            SELECT id, 
            version, 
            image, 
            expired_at 
            FROM claro_badge
        ");
        $this->addSql("
            DROP TABLE claro_badge
        ");
        $this->addSql("
            CREATE TABLE claro_badge (
                id INTEGER NOT NULL, 
                version INTEGER NOT NULL, 
                image VARCHAR(255) NOT NULL, 
                expired_at DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge (id, version, image, expired_at) 
            SELECT id, 
            version, 
            image, 
            expired_at 
            FROM __temp__claro_badge
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge
        ");
    }
}