<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/20 03:02:59
 */
class Version20141120150258 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_B1ADDDB582D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_B1ADDDB5A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_event AS 
            SELECT id, 
            workspace_id, 
            user_id, 
            title, 
            start_date, 
            end_date, 
            description, 
            allday, 
            priority 
            FROM claro_event
        ");
        $this->addSql("
            DROP TABLE claro_event
        ");
        $this->addSql("
            CREATE TABLE claro_event (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER NOT NULL, 
                title VARCHAR(50) NOT NULL, 
                start_date INTEGER DEFAULT NULL, 
                end_date INTEGER DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                priority VARCHAR(255) DEFAULT NULL, 
                allday BOOLEAN NOT NULL, 
                istask BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_B1ADDDB582D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_B1ADDDB5A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_event (
                id, workspace_id, user_id, title, start_date, 
                end_date, description, allday, priority
            ) 
            SELECT id, 
            workspace_id, 
            user_id, 
            title, 
            start_date, 
            end_date, 
            description, 
            allday, 
            priority 
            FROM __temp__claro_event
        ");
        $this->addSql("
            DROP TABLE __temp__claro_event
        ");
        $this->addSql("
            CREATE INDEX IDX_B1ADDDB582D40A1F ON claro_event (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B1ADDDB5A76ED395 ON claro_event (user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_B1ADDDB582D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_B1ADDDB5A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_event AS 
            SELECT id, 
            workspace_id, 
            user_id, 
            title, 
            start_date, 
            end_date, 
            description, 
            allday, 
            priority 
            FROM claro_event
        ");
        $this->addSql("
            DROP TABLE claro_event
        ");
        $this->addSql("
            CREATE TABLE claro_event (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER NOT NULL, 
                title VARCHAR(50) NOT NULL, 
                start_date INTEGER DEFAULT NULL, 
                end_date INTEGER DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                priority VARCHAR(255) DEFAULT NULL, 
                allday BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_B1ADDDB582D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_B1ADDDB5A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_event (
                id, workspace_id, user_id, title, start_date, 
                end_date, description, allday, priority
            ) 
            SELECT id, 
            workspace_id, 
            user_id, 
            title, 
            start_date, 
            end_date, 
            description, 
            allday, 
            priority 
            FROM __temp__claro_event
        ");
        $this->addSql("
            DROP TABLE __temp__claro_event
        ");
        $this->addSql("
            CREATE INDEX IDX_B1ADDDB582D40A1F ON claro_event (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B1ADDDB5A76ED395 ON claro_event (user_id)
        ");
    }
}