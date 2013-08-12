<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/07 04:30:19
 */
class Version20130807163019 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
            path, 
            mime_type 
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
                mime_type VARCHAR(255) DEFAULT NULL, 
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
                modification_date, name, lvl, path, 
                mime_type
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
            path, 
            mime_type 
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
            CREATE TEMPORARY TABLE __temp__claro_activity AS 
            SELECT id, 
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
                id, instruction, start_date, end_date
            ) 
            SELECT id, 
            instruction, 
            start_date, 
            end_date 
            FROM __temp__claro_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC460D9FD7 ON claro_activity (node_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BE1F029B6
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_file AS 
            SELECT id, 
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
                node_id INTEGER DEFAULT NULL, 
                size INTEGER NOT NULL, 
                hash_name VARCHAR(36) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EA81C80B460D9FD7 FOREIGN KEY (node_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_file (id, size, hash_name) 
            SELECT id, 
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
            CREATE UNIQUE INDEX UNIQ_EA81C80B460D9FD7 ON claro_file (node_id)
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_link AS 
            SELECT id, 
            url 
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
            INSERT INTO claro_link (id, url) 
            SELECT id, 
            url 
            FROM __temp__claro_link
        ");
        $this->addSql("
            DROP TABLE __temp__claro_link
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EA460D9FD7 ON claro_link (node_id)
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_directory AS 
            SELECT id 
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
            INSERT INTO claro_directory (id) 
            SELECT id 
            FROM __temp__claro_directory
        ");
        $this->addSql("
            DROP TABLE __temp__claro_directory
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186460D9FD7 ON claro_directory (node_id)
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB889329D25
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_shortcut AS 
            SELECT id, 
            resource_id 
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
            resource_id 
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8460D9FD7 ON claro_resource_shortcut (node_id)
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_text AS 
            SELECT id, 
            version 
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
            INSERT INTO claro_text (id, version) 
            SELECT id, 
            version 
            FROM __temp__claro_text
        ");
        $this->addSql("
            DROP TABLE __temp__claro_text
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DC460D9FD7 ON claro_text (node_id)
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F12D3860F
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FCD53EDB6
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FC6F122B2
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F7E3C61F9
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F98EC6B7B
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FD60322AC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log AS 
            SELECT id, 
            role_id, 
            doer_id, 
            owner_id, 
            workspace_id, 
            resource_id, 
            resource_type_id, 
            receiver_group_id, 
            receiver_id, 
            \"action\", 
            date_log, 
            short_date_log, 
            details, 
            doer_type, 
            doer_ip, 
            tool_name, 
            child_type, 
            child_action 
            FROM claro_log
        ");
        $this->addSql("
            DROP TABLE claro_log
        ");
        $this->addSql("
            CREATE TABLE claro_log (
                id INTEGER NOT NULL, 
                role_id INTEGER DEFAULT NULL, 
                doer_id INTEGER DEFAULT NULL, 
                owner_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                receiver_group_id INTEGER DEFAULT NULL, 
                receiver_id INTEGER DEFAULT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                date_log DATETIME NOT NULL, 
                short_date_log DATE NOT NULL, 
                details CLOB DEFAULT NULL, 
                doer_type VARCHAR(255) NOT NULL, 
                doer_ip VARCHAR(255) DEFAULT NULL, 
                tool_name VARCHAR(255) DEFAULT NULL, 
                child_type VARCHAR(255) DEFAULT NULL, 
                child_action VARCHAR(255) DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_97FAB91FD60322AC FOREIGN KEY (role_id) 
                REFERENCES claro_role (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F12D3860F FOREIGN KEY (doer_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F7E3C61F9 FOREIGN KEY (owner_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FC6F122B2 FOREIGN KEY (receiver_group_id) 
                REFERENCES claro_group (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FCD53EDB6 FOREIGN KEY (receiver_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log (
                id, role_id, doer_id, owner_id, workspace_id, 
                resourceNode_id, resource_type_id, 
                receiver_group_id, receiver_id, 
                \"action\", date_log, short_date_log, 
                details, doer_type, doer_ip, tool_name, 
                child_type, child_action
            ) 
            SELECT id, 
            role_id, 
            doer_id, 
            owner_id, 
            workspace_id, 
            resource_id, 
            resource_type_id, 
            receiver_group_id, 
            receiver_id, 
            \"action\", 
            date_log, 
            short_date_log, 
            details, 
            doer_type, 
            doer_ip, 
            tool_name, 
            child_type, 
            child_action 
            FROM __temp__claro_log
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F12D3860F ON claro_log (doer_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FCD53EDB6 ON claro_log (receiver_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FC6F122B2 ON claro_log (receiver_group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F7E3C61F9 ON claro_log (owner_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F82D40A1F ON claro_log (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F98EC6B7B ON claro_log (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FD60322AC ON claro_log (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FB87FAB32 ON claro_log (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity AS 
            SELECT id, 
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CACBF396750 FOREIGN KEY (id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (
                id, instruction, start_date, end_date
            ) 
            SELECT id, 
            instruction, 
            start_date, 
            end_date 
            FROM __temp__claro_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity
        ");
        $this->addSql("
            DROP INDEX UNIQ_12EEC186460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_directory AS 
            SELECT id 
            FROM claro_directory
        ");
        $this->addSql("
            DROP TABLE claro_directory
        ");
        $this->addSql("
            CREATE TABLE claro_directory (
                id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_12EEC186BF396750 FOREIGN KEY (id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_directory (id) 
            SELECT id 
            FROM __temp__claro_directory
        ");
        $this->addSql("
            DROP TABLE __temp__claro_directory
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_EA81C80BBF396750 FOREIGN KEY (id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_file (id, size, hash_name) 
            SELECT id, 
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
            DROP INDEX UNIQ_50B267EA460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_link AS 
            SELECT id, 
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_50B267EABF396750 FOREIGN KEY (id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_link (id, url) 
            SELECT id, 
            url 
            FROM __temp__claro_link
        ");
        $this->addSql("
            DROP TABLE __temp__claro_link
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F12D3860F
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FCD53EDB6
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FC6F122B2
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F7E3C61F9
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F98EC6B7B
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FD60322AC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log AS 
            SELECT id, 
            doer_id, 
            receiver_id, 
            receiver_group_id, 
            owner_id, 
            workspace_id, 
            resource_type_id, 
            role_id, 
            \"action\", 
            date_log, 
            short_date_log, 
            details, 
            doer_type, 
            doer_ip, 
            tool_name, 
            child_type, 
            child_action, 
            resourceNode_id 
            FROM claro_log
        ");
        $this->addSql("
            DROP TABLE claro_log
        ");
        $this->addSql("
            CREATE TABLE claro_log (
                id INTEGER NOT NULL, 
                doer_id INTEGER DEFAULT NULL, 
                receiver_id INTEGER DEFAULT NULL, 
                receiver_group_id INTEGER DEFAULT NULL, 
                owner_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                role_id INTEGER DEFAULT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                date_log DATETIME NOT NULL, 
                short_date_log DATE NOT NULL, 
                details CLOB DEFAULT NULL, 
                doer_type VARCHAR(255) NOT NULL, 
                doer_ip VARCHAR(255) DEFAULT NULL, 
                tool_name VARCHAR(255) DEFAULT NULL, 
                child_type VARCHAR(255) DEFAULT NULL, 
                child_action VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_97FAB91F12D3860F FOREIGN KEY (doer_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FCD53EDB6 FOREIGN KEY (receiver_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FC6F122B2 FOREIGN KEY (receiver_group_id) 
                REFERENCES claro_group (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F7E3C61F9 FOREIGN KEY (owner_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FD60322AC FOREIGN KEY (role_id) 
                REFERENCES claro_role (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log (
                id, doer_id, receiver_id, receiver_group_id, 
                owner_id, workspace_id, resource_type_id, 
                role_id, \"action\", date_log, short_date_log, 
                details, doer_type, doer_ip, tool_name, 
                child_type, child_action, resource_id
            ) 
            SELECT id, 
            doer_id, 
            receiver_id, 
            receiver_group_id, 
            owner_id, 
            workspace_id, 
            resource_type_id, 
            role_id, 
            \"action\", 
            date_log, 
            short_date_log, 
            details, 
            doer_type, 
            doer_ip, 
            tool_name, 
            child_type, 
            child_action, 
            resourceNode_id 
            FROM __temp__claro_log
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F12D3860F ON claro_log (doer_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FCD53EDB6 ON claro_log (receiver_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FC6F122B2 ON claro_log (receiver_group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F7E3C61F9 ON claro_log (owner_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F82D40A1F ON claro_log (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F98EC6B7B ON claro_log (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FD60322AC ON claro_log (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F89329D25 ON claro_log (resource_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource 
            ADD COLUMN discr VARCHAR(255) NOT NULL
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
                resource_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5E7F4AB8BF396750 FOREIGN KEY (id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_5E7F4AB889329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_shortcut (id, resource_id) 
            SELECT id, 
            node_id 
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB889329D25 ON claro_resource_shortcut (resource_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DC460D9FD7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_text AS 
            SELECT id, 
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_5D9559DCBF396750 FOREIGN KEY (id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_text (id, version) 
            SELECT id, 
            version 
            FROM __temp__claro_text
        ");
        $this->addSql("
            DROP TABLE __temp__claro_text
        ");
    }
}