<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/20 08:07:27
 */
class Version20131020200726 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_workspace_favourite (
                id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_711A30B82D40A1F ON claro_workspace_favourite (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_711A30BA76ED395 ON claro_workspace_favourite (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX workspace_favourite_unique_combination ON claro_workspace_favourite (workspace_id, user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD COLUMN result VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD COLUMN resultComparison INTEGER DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_workspace_favourite
        ");
        $this->addSql("
            DROP INDEX IDX_805FCB8FF7A2C2FC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_badge_rule AS 
            SELECT id, 
            badge_id, 
            occurrence, 
            \"action\" 
            FROM claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id INTEGER NOT NULL, 
                badge_id INTEGER NOT NULL, 
                occurrence INTEGER NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge_rule (
                id, badge_id, occurrence, \"action\"
            ) 
            SELECT id, 
            badge_id, 
            occurrence, 
            \"action\" 
            FROM __temp__claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge_rule
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
    }
}