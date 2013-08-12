<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/07 05:06:01
 */
class Version20130807170601 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483D60322AC
        ");
        $this->addSql("
            DROP INDEX IDX_3848F48389329D25
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_rights AS 
            SELECT id, 
            resource_id, 
            role_id, 
            can_delete, 
            can_open, 
            can_edit, 
            can_copy, 
            can_export 
            FROM claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE claro_resource_rights
        ");
        $this->addSql("
            CREATE TABLE claro_resource_rights (
                id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                can_delete BOOLEAN NOT NULL, 
                can_open BOOLEAN NOT NULL, 
                can_edit BOOLEAN NOT NULL, 
                can_copy BOOLEAN NOT NULL, 
                can_export BOOLEAN NOT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3848F483D60322AC FOREIGN KEY (role_id) 
                REFERENCES claro_role (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_rights (
                id, resourceNode_id, role_id, can_delete, 
                can_open, can_edit, can_copy, can_export
            ) 
            SELECT id, 
            resource_id, 
            role_id, 
            can_delete, 
            can_open, 
            can_edit, 
            can_copy, 
            can_export 
            FROM __temp__claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_rights
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_rights_unique_resource_role ON claro_resource_rights (resourceNode_id, role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483D60322AC ON claro_resource_rights (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483B87FAB32 ON claro_resource_rights (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_F44381E0AA23F6C8
        ");
        $this->addSql("
            DROP INDEX UNIQ_F44381E02DE62210
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
            CREATE TEMPORARY TABLE __temp__claro_resource AS 
            SELECT id, 
            previous_id, 
            license_id, 
            icon_id, 
            parent_id, 
            workspace_id, 
            resource_type_id, 
            user_id, 
            next_id, 
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
                previous_id INTEGER DEFAULT NULL, 
                license_id INTEGER DEFAULT NULL, 
                icon_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER NOT NULL, 
                resource_type_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INTEGER DEFAULT NULL, 
                path VARCHAR(3000) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F44381E02DE62210 FOREIGN KEY (previous_id) 
                REFERENCES claro_resource (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E0460F904B FOREIGN KEY (license_id) 
                REFERENCES claro_license (id) 
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
                CONSTRAINT FK_F44381E098EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E0A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F44381E0AA23F6C8 FOREIGN KEY (next_id) 
                REFERENCES claro_resource (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource (
                id, previous_id, license_id, icon_id, 
                parent_id, workspace_id, resource_type_id, 
                user_id, next_id, creation_date, 
                modification_date, name, lvl, path
            ) 
            SELECT id, 
            previous_id, 
            license_id, 
            icon_id, 
            parent_id, 
            workspace_id, 
            resource_type_id, 
            user_id, 
            next_id, 
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
            CREATE UNIQUE INDEX UNIQ_F44381E0AA23F6C8 ON claro_resource (next_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F44381E02DE62210 ON claro_resource (previous_id)
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
            DROP INDEX UNIQ_E4A67CAC460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity AS 
            SELECT id, 
            node_id, 
            instruction, 
            start_date, 
            end_date 
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
                mime_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (
                id, resourceNode_id, instruction, 
                start_date, end_date
            ) 
            SELECT id, 
            node_id, 
            instruction, 
            start_date, 
            end_date 
            FROM __temp__claro_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX resource_activity_unique_combination
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7E81C06096
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7E89329D25
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_activity AS 
            SELECT id, 
            resource_id, 
            activity_id, 
            sequence_order 
            FROM claro_resource_activity
        ");
        $this->addSql("
            DROP TABLE claro_resource_activity
        ");
        $this->addSql("
            CREATE TABLE claro_resource_activity (
                id INTEGER NOT NULL, 
                activity_id INTEGER NOT NULL, 
                sequence_order INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_DCF37C7E81C06096 FOREIGN KEY (activity_id) 
                REFERENCES claro_activity (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_DCF37C7EB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_activity (
                id, resourceNode_id, activity_id, 
                sequence_order
            ) 
            SELECT id, 
            resource_id, 
            activity_id, 
            sequence_order 
            FROM __temp__claro_resource_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_activity
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_activity_unique_combination ON claro_resource_activity (activity_id, resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7E81C06096 ON claro_resource_activity (activity_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7EB87FAB32 ON claro_resource_activity (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BE1F029B6
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80B460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_file AS 
            SELECT id, 
            node_id, 
            size, 
            hash_name 
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
                mime_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_file (
                id, resourceNode_id, size, hash_name
            ) 
            SELECT id, 
            node_id, 
            size, 
            hash_name 
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
            DROP INDEX UNIQ_50B267EA460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_link AS 
            SELECT id, 
            node_id, 
            url 
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
                mime_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_link (id, resourceNode_id, url) 
            SELECT id, 
            node_id, 
            url 
            FROM __temp__claro_link
        ");
        $this->addSql("
            DROP TABLE __temp__claro_link
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EAB87FAB32 ON claro_link (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_12EEC186460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_directory AS 
            SELECT id, 
            node_id 
            FROM claro_directory
        ");
        $this->addSql("
            DROP TABLE claro_directory
        ");
        $this->addSql("
            CREATE TABLE claro_directory (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_directory (id, resourceNode_id) 
            SELECT id, 
            node_id 
            FROM __temp__claro_directory
        ");
        $this->addSql("
            DROP TABLE __temp__claro_directory
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186B87FAB32 ON claro_directory (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_shortcut AS 
            SELECT id, 
            node_id 
            FROM claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE claro_resource_shortcut
        ");
        $this->addSql("
            CREATE TABLE claro_resource_shortcut (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_shortcut (id, resourceNode_id) 
            SELECT id, 
            node_id 
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DC460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_text AS 
            SELECT id, 
            node_id, 
            version 
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
                mime_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_text (id, resourceNode_id, version) 
            SELECT id, 
            node_id, 
            version 
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
                node_id INTEGER DEFAULT NULL, 
                instruction VARCHAR(255) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CAC460D9FD7 FOREIGN KEY (node_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (
                id, instruction, start_date, end_date, 
                node_id
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
            CREATE UNIQUE INDEX UNIQ_E4A67CAC460D9FD7 ON claro_activity (node_id)
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
                node_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_12EEC186460D9FD7 FOREIGN KEY (node_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_directory (id, node_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_directory
        ");
        $this->addSql("
            DROP TABLE __temp__claro_directory
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186460D9FD7 ON claro_directory (node_id)
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
                node_id INTEGER DEFAULT NULL, 
                size INTEGER NOT NULL, 
                hash_name VARCHAR(36) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EA81C80B460D9FD7 FOREIGN KEY (node_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_file (id, size, hash_name, node_id) 
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
            CREATE UNIQUE INDEX UNIQ_EA81C80B460D9FD7 ON claro_file (node_id)
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
                node_id INTEGER DEFAULT NULL, 
                url VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_50B267EA460D9FD7 FOREIGN KEY (node_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_link (id, url, node_id) 
            SELECT id, 
            url, 
            resourceNode_id 
            FROM __temp__claro_link
        ");
        $this->addSql("
            DROP TABLE __temp__claro_link
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EA460D9FD7 ON claro_link (node_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource 
            ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7E81C06096
        ");
        $this->addSql("
            DROP INDEX IDX_DCF37C7EB87FAB32
        ");
        $this->addSql("
            DROP INDEX resource_activity_unique_combination
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_activity AS 
            SELECT id, 
            activity_id, 
            sequence_order, 
            resourceNode_id 
            FROM claro_resource_activity
        ");
        $this->addSql("
            DROP TABLE claro_resource_activity
        ");
        $this->addSql("
            CREATE TABLE claro_resource_activity (
                id INTEGER NOT NULL, 
                activity_id INTEGER NOT NULL, 
                resource_id INTEGER NOT NULL, 
                sequence_order INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_DCF37C7E81C06096 FOREIGN KEY (activity_id) 
                REFERENCES claro_activity (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_DCF37C7E89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_activity (
                id, activity_id, sequence_order, resource_id
            ) 
            SELECT id, 
            activity_id, 
            sequence_order, 
            resourceNode_id 
            FROM __temp__claro_resource_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_activity
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7E81C06096 ON claro_resource_activity (activity_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_activity_unique_combination ON claro_resource_activity (activity_id, resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7E89329D25 ON claro_resource_activity (resource_id)
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483D60322AC
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483B87FAB32
        ");
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_rights AS 
            SELECT id, 
            role_id, 
            can_delete, 
            can_open, 
            can_edit, 
            can_copy, 
            can_export, 
            resourceNode_id 
            FROM claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE claro_resource_rights
        ");
        $this->addSql("
            CREATE TABLE claro_resource_rights (
                id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                resource_id INTEGER NOT NULL, 
                can_delete BOOLEAN NOT NULL, 
                can_open BOOLEAN NOT NULL, 
                can_edit BOOLEAN NOT NULL, 
                can_copy BOOLEAN NOT NULL, 
                can_export BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3848F483D60322AC FOREIGN KEY (role_id) 
                REFERENCES claro_role (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_3848F48389329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_rights (
                id, role_id, can_delete, can_open, 
                can_edit, can_copy, can_export, resource_id
            ) 
            SELECT id, 
            role_id, 
            can_delete, 
            can_open, 
            can_edit, 
            can_copy, 
            can_export, 
            resourceNode_id 
            FROM __temp__claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_rights
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483D60322AC ON claro_resource_rights (role_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_rights_unique_resource_role ON claro_resource_rights (resource_id, role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F48389329D25 ON claro_resource_rights (resource_id)
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
                node_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5E7F4AB8460D9FD7 FOREIGN KEY (node_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_shortcut (id, node_id) 
            SELECT id, 
            resourceNode_id 
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8460D9FD7 ON claro_resource_shortcut (node_id)
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
                node_id INTEGER DEFAULT NULL, 
                version INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5D9559DC460D9FD7 FOREIGN KEY (node_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_text (id, version, node_id) 
            SELECT id, 
            version, 
            resourceNode_id 
            FROM __temp__claro_text
        ");
        $this->addSql("
            DROP TABLE __temp__claro_text
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DC460D9FD7 ON claro_text (node_id)
        ");
    }
}