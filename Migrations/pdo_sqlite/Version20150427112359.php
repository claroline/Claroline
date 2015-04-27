<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/27 11:24:01
 */
class Version20150427112359 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_A76799FF460F904B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF98EC6B7B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF61220EA6
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF54B9D732
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_node AS 
            SELECT id, 
            icon_id, 
            creator_id, 
            parent_id, 
            workspace_id, 
            resource_type_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path, 
            mime_type, 
            class, 
            accessible_from, 
            accessible_until, 
            published, 
            value 
            FROM claro_resource_node
        ");
        $this->addSql("
            DROP TABLE claro_resource_node
        ");
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INTEGER NOT NULL, 
                icon_id INTEGER DEFAULT NULL, 
                creator_id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER NOT NULL, 
                resource_type_id INTEGER NOT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INTEGER DEFAULT NULL, 
                path VARCHAR(3000) DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                class VARCHAR(256) NOT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                published BOOLEAN DEFAULT '1' NOT NULL, 
                value INTEGER DEFAULT NULL, 
                license VARCHAR(255) DEFAULT NULL, 
                author VARCHAR(255) DEFAULT NULL, 
                active BOOLEAN DEFAULT '1' NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
                REFERENCES claro_resource_icon (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_node (
                id, icon_id, creator_id, parent_id, 
                workspace_id, resource_type_id, 
                creation_date, modification_date, 
                name, lvl, path, mime_type, class, 
                accessible_from, accessible_until, 
                published, value
            ) 
            SELECT id, 
            icon_id, 
            creator_id, 
            parent_id, 
            workspace_id, 
            resource_type_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path, 
            mime_type, 
            class, 
            accessible_from, 
            accessible_until, 
            published, 
            value 
            FROM __temp__claro_resource_node
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_node
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF98EC6B7B ON claro_resource_node (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF61220EA6 ON claro_resource_node (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF54B9D732 ON claro_resource_node (icon_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF727ACA70 ON claro_resource_node (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF82D40A1F ON claro_resource_node (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_A76799FF98EC6B7B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF61220EA6
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF54B9D732
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_node AS 
            SELECT id, 
            resource_type_id, 
            creator_id, 
            icon_id, 
            parent_id, 
            workspace_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path, 
            value, 
            mime_type, 
            class, 
            accessible_from, 
            accessible_until, 
            published 
            FROM claro_resource_node
        ");
        $this->addSql("
            DROP TABLE claro_resource_node
        ");
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER NOT NULL, 
                creator_id INTEGER NOT NULL, 
                icon_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER NOT NULL, 
                license_id INTEGER DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INTEGER DEFAULT NULL, 
                path VARCHAR(3000) DEFAULT NULL, 
                value INTEGER DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                class VARCHAR(256) NOT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                published BOOLEAN DEFAULT '1' NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A76799FF98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
                REFERENCES claro_resource_icon (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id) 
                REFERENCES claro_license (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_node (
                id, resource_type_id, creator_id, 
                icon_id, parent_id, workspace_id, 
                creation_date, modification_date, 
                name, lvl, path, value, mime_type, 
                class, accessible_from, accessible_until, 
                published
            ) 
            SELECT id, 
            resource_type_id, 
            creator_id, 
            icon_id, 
            parent_id, 
            workspace_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path, 
            value, 
            mime_type, 
            class, 
            accessible_from, 
            accessible_until, 
            published 
            FROM __temp__claro_resource_node
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_node
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF98EC6B7B ON claro_resource_node (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF61220EA6 ON claro_resource_node (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF54B9D732 ON claro_resource_node (icon_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF727ACA70 ON claro_resource_node (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF82D40A1F ON claro_resource_node (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
        ");
    }
}