<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/07 05:34:18
 */
class Version20130807173418 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity AS 
            SELECT id, 
            instruction, 
            start_date, 
            end_date, 
            resourceNode_id 
            FROM claro_activity
        ");
        $this->addSql("
            DROP TABLE claro_activity
        ");
        $this->addSql("
            CREATE TABLE claro_activity (
                id INTEGER NOT NULL, 
                instruction VARCHAR(255) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (
                id, instruction, start_date, end_date, 
                resourceNode_id
            ) 
            SELECT id, 
            instruction, 
            start_date, 
            end_date, 
            resourceNode_id 
            FROM __temp__claro_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BE1F029B6
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_file AS 
            SELECT id, 
            size, 
            hash_name, 
            resourceNode_id 
            FROM claro_file
        ");
        $this->addSql("
            DROP TABLE claro_file
        ");
        $this->addSql("
            CREATE TABLE claro_file (
                id INTEGER NOT NULL, 
                size INTEGER NOT NULL, 
                hash_name VARCHAR(36) NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_file (
                id, size, hash_name, resourceNode_id
            ) 
            SELECT id, 
            size, 
            hash_name, 
            resourceNode_id 
            FROM __temp__claro_file
        ");
        $this->addSql("
            DROP TABLE __temp__claro_file
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BE1F029B6 ON claro_file (hash_name)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_50B267EAB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_link AS 
            SELECT id, 
            url, 
            resourceNode_id 
            FROM claro_link
        ");
        $this->addSql("
            DROP TABLE claro_link
        ");
        $this->addSql("
            CREATE TABLE claro_link (
                id INTEGER NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_link (id, url, resourceNode_id) 
            SELECT id, 
            url, 
            resourceNode_id 
            FROM __temp__claro_link
        ");
        $this->addSql("
            DROP TABLE __temp__claro_link
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EAB87FAB32 ON claro_link (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_12EEC186B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_directory AS 
            SELECT id, 
            resourceNode_id 
            FROM claro_directory
        ");
        $this->addSql("
            DROP TABLE claro_directory
        ");
        $this->addSql("
            CREATE TABLE claro_directory (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_directory (id, resourceNode_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_directory
        ");
        $this->addSql("
            DROP TABLE __temp__claro_directory
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186B87FAB32 ON claro_directory (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_shortcut AS 
            SELECT id, 
            resourceNode_id 
            FROM claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE claro_resource_shortcut
        ");
        $this->addSql("
            CREATE TABLE claro_resource_shortcut (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_shortcut (id, resourceNode_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DCB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_text AS 
            SELECT id, 
            version, 
            resourceNode_id 
            FROM claro_text
        ");
        $this->addSql("
            DROP TABLE claro_text
        ");
        $this->addSql("
            CREATE TABLE claro_text (
                id INTEGER NOT NULL, 
                version INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_text (id, version, resourceNode_id) 
            SELECT id, 
            version, 
            resourceNode_id 
            FROM __temp__claro_text
        ");
        $this->addSql("
            DROP TABLE __temp__claro_text
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DCB87FAB32 ON claro_text (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_F44381E0460F904B
        ");
        $this->addSql("
            DROP INDEX IDX_F44381E098EC6B7B
        ");
        $this->addSql("
            DROP INDEX IDX_F44381E0A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F44381E054B9D732
        ");
        $this->addSql("
            DROP INDEX IDX_F44381E0727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_F44381E082D40A1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_F44381E0AA23F6C8
        ");
        $this->addSql("
            DROP INDEX UNIQ_F44381E02DE62210
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource AS 
            SELECT id, 
            license_id, 
            resource_type_id, 
            user_id, 
            icon_id, 
            parent_id, 
            workspace_id, 
            next_id, 
            previous_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path 
            FROM claro_resource
        ");
        $this->addSql("
            DROP TABLE claro_resource
        ");
        $this->addSql("
            CREATE TABLE claro_resource (
                id INTEGER NOT NULL, 
                license_id INTEGER DEFAULT NULL, 
                resource_type_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                icon_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                previous_id INTEGER DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INTEGER DEFAULT NULL, 
                path VARCHAR(3000) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F44381E0460F904B FOREIGN KEY (license_id) 
                REFERENCES claro_license (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E098EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E0A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E054B9D732 FOREIGN KEY (icon_id) 
                REFERENCES claro_resource_icon (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E0727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E082D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E0AA23F6C8 FOREIGN KEY (next_id) 
                REFERENCES claro_resource (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E02DE62210 FOREIGN KEY (previous_id) 
                REFERENCES claro_resource (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource (
                id, license_id, resource_type_id, 
                user_id, icon_id, parent_id, workspace_id, 
                next_id, previous_id, creation_date, 
                modification_date, name, lvl, path
            ) 
            SELECT id, 
            license_id, 
            resource_type_id, 
            user_id, 
            icon_id, 
            parent_id, 
            workspace_id, 
            next_id, 
            previous_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path 
            FROM __temp__claro_resource
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource
        ");
        $this->addSql("
            CREATE INDEX IDX_F44381E0460F904B ON claro_resource (license_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F44381E098EC6B7B ON claro_resource (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F44381E0A76ED395 ON claro_resource (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F44381E054B9D732 ON claro_resource (icon_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F44381E0727ACA70 ON claro_resource (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F44381E082D40A1F ON claro_resource (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F44381E0AA23F6C8 ON claro_resource (next_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F44381E02DE62210 ON claro_resource (previous_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
    }
}