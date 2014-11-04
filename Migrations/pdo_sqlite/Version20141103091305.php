<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/03 09:13:07
 */
class Version20141103091305 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_536FFC4C82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_workspace_model AS 
            SELECT id, 
            workspace_id, 
            name 
            FROM claro_workspace_model
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_536FFC4C82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_workspace_model (id, workspace_id, name) 
            SELECT id, 
            workspace_id, 
            name 
            FROM __temp__claro_workspace_model
        ");
        $this->addSql("
            DROP TABLE __temp__claro_workspace_model
        ");
        $this->addSql("
            CREATE INDEX IDX_536FFC4C82D40A1F ON claro_workspace_model (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_536FFC4C5E237E06 ON claro_workspace_model (name)
        ");
        $this->addSql("
            ALTER TABLE claro_type 
            ADD COLUMN publish BOOLEAN DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_type AS 
            SELECT id, 
            name, 
            max_content_page 
            FROM claro_type
        ");
        $this->addSql("
            DROP TABLE claro_type
        ");
        $this->addSql("
            CREATE TABLE claro_type (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                max_content_page INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_type (id, name, max_content_page) 
            SELECT id, 
            name, 
            max_content_page 
            FROM __temp__claro_type
        ");
        $this->addSql("
            DROP TABLE __temp__claro_type
        ");
        $this->addSql("
            DROP INDEX UNIQ_536FFC4C5E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_536FFC4C82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_workspace_model AS 
            SELECT id, 
            workspace_id, 
            name 
            FROM claro_workspace_model
        ");
        $this->addSql("
            DROP TABLE claro_workspace_model
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_model (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_536FFC4C82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_workspace_model (id, workspace_id, name) 
            SELECT id, 
            workspace_id, 
            name 
            FROM __temp__claro_workspace_model
        ");
        $this->addSql("
            DROP TABLE __temp__claro_workspace_model
        ");
        $this->addSql("
            CREATE INDEX IDX_536FFC4C82D40A1F ON claro_workspace_model (workspace_id)
        ");
    }
}