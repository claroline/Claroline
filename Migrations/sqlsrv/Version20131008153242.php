<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/08 03:32:46
 */
class Version20131008153242 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id INT IDENTITY NOT NULL, 
                badge_id INT NOT NULL, 
                occurrence SMALLINT NOT NULL, 
                action NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            ADD CONSTRAINT FK_487A496AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            ADD CONSTRAINT FK_487A496AF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_translation 
            ADD CONSTRAINT FK_849BC831F7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD workspace_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD automatic_award BIT
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD CONSTRAINT FK_74F39F0F82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_74F39F0F82D40A1F ON claro_badge (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP COLUMN workspace_id
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP COLUMN automatic_award
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP CONSTRAINT FK_74F39F0F82D40A1F
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_74F39F0F82D40A1F'
            ) 
            ALTER TABLE claro_badge 
            DROP CONSTRAINT IDX_74F39F0F82D40A1F ELSE 
            DROP INDEX IDX_74F39F0F82D40A1F ON claro_badge
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            DROP CONSTRAINT FK_487A496AA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim 
            DROP CONSTRAINT FK_487A496AF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_translation 
            DROP CONSTRAINT FK_849BC831F7A2C2FC
        ");
    }
}