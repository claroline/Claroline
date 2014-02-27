<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/26 02:50:57
 */
class Version20140226145055 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
    }
}