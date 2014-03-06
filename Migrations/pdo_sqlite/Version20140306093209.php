<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/06 09:32:11
 */
class Version20140306093209 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD COLUMN deletedAt DATETIME DEFAULT NULL
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
            is_expiring, 
            expire_duration, 
            expire_period 
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
                id, workspace_id, version, automatic_award, 
                image, is_expiring, expire_duration, 
                expire_period
            ) 
            SELECT id, 
            workspace_id, 
            version, 
            automatic_award, 
            image, 
            is_expiring, 
            expire_duration, 
            expire_period 
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