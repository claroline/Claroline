<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/19 02:56:25
 */
class Version20130819145625 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INTEGER NOT NULL,
                license_id INTEGER DEFAULT NULL,
                resource_type_id INTEGER NOT NULL,
                creator_id INTEGER NOT NULL,
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
                mime_type VARCHAR(255) DEFAULT NULL,
                class VARCHAR(256) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
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
            CREATE UNIQUE INDEX UNIQ_A76799FFAA23F6C8 ON claro_resource_node (next_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FF2DE62210 ON claro_resource_node (previous_id)
        ");
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
                REFERENCES claro_resource_node (id)
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
            DROP INDEX UNIQ_AEC626935E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_AEC62693EC942BCF
        ");
        $this->addSql("
            DROP INDEX IDX_AEC62693727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_type AS
            SELECT id,
            plugin_id,
            name,
            is_exportable
            FROM claro_resource_type
        ");
        $this->addSql("
            DROP TABLE claro_resource_type
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type (
                id INTEGER NOT NULL,
                plugin_id INTEGER DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                is_exportable BOOLEAN NOT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_AEC62693EC942BCF FOREIGN KEY (plugin_id)
                REFERENCES claro_plugin (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_type (
                id, plugin_id, name, is_exportable
            )
            SELECT id,
            plugin_id,
            name,
            is_exportable
            FROM __temp__claro_resource_type
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_type
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_AEC626935E237E06 ON claro_resource_type (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693EC942BCF ON claro_resource_type (plugin_id)
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
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id)
                REFERENCES claro_resource_node (id)
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
                REFERENCES claro_resource_node (id)
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
                hash_name VARCHAR(50) NOT NULL,
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id)
                REFERENCES claro_resource_node (id)
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
            CREATE UNIQUE INDEX UNIQ_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
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
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id)
                REFERENCES claro_resource_node (id)
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
            CREATE UNIQUE INDEX UNIQ_50B267EAB87FAB32 ON claro_link (resourceNode_id)
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
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id)
                REFERENCES claro_resource_node (id)
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
            CREATE UNIQUE INDEX UNIQ_12EEC186B87FAB32 ON claro_directory (resourceNode_id)
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
                target_id INTEGER NOT NULL,
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id)
                REFERENCES claro_resource_node (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id)
                REFERENCES claro_resource_node (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_shortcut (id, target_id)
            SELECT id,
            resource_id
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut (target_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
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
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id)
                REFERENCES claro_resource_node (id)
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
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DCB87FAB32 ON claro_text (resourceNode_id)
        ");
        $this->addSql("
            DROP INDEX IDX_F61948DE698D3548
        ");
        $this->addSql("
            DROP INDEX IDX_F61948DEA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_text_revision AS
            SELECT id,
            user_id,
            text_id,
            version,
            content
            FROM claro_text_revision
        ");
        $this->addSql("
            DROP TABLE claro_text_revision
        ");
        $this->addSql("
            CREATE TABLE claro_text_revision (
                id INTEGER NOT NULL,
                user_id INTEGER DEFAULT NULL,
                text_id INTEGER DEFAULT NULL,
                version INTEGER NOT NULL,
                content CLOB NOT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_F61948DEA76ED395 FOREIGN KEY (user_id)
                REFERENCES claro_user (id)
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_F61948DE698D3548 FOREIGN KEY (text_id)
                REFERENCES claro_text (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_text_revision (
                id, user_id, text_id, version, content
            )
            SELECT id,
            user_id,
            text_id,
            version,
            content
            FROM __temp__claro_text_revision
        ");
        $this->addSql("
            DROP TABLE __temp__claro_text_revision
        ");
        $this->addSql("
            CREATE INDEX IDX_F61948DE698D3548 ON claro_text_revision (text_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F61948DEA76ED395 ON claro_text_revision (user_id)
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
                REFERENCES claro_resource_node (id)
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
            DROP TABLE claro_resource_node
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32
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
            DROP INDEX UNIQ_12EEC186B87FAB32
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
            DROP INDEX UNIQ_EA81C80BB87FAB32
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
            DROP INDEX UNIQ_50B267EAB87FAB32
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
                resource_id INTEGER DEFAULT NULL,
                resource_type_id INTEGER DEFAULT NULL,
                role_id INTEGER DEFAULT NULL,
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
                CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resource_id)
                REFERENCES claro_resource_node (id)
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
                CONSTRAINT FK_DCF37C7EB87FAB32 FOREIGN KEY (resource_id)
                REFERENCES claro_resource_node (id)
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
                CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resource_id)
                REFERENCES claro_resource_node (id)
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
            DROP INDEX IDX_5E7F4AB8158E0B66
        ");
        $this->addSql("
            DROP INDEX UNIQ_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_shortcut AS
            SELECT id,
            target_id
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
                CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (resource_id)
                REFERENCES claro_resource_node (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
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
            target_id
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB889329D25 ON claro_resource_shortcut (resource_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_AEC626935E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_AEC62693EC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_type AS
            SELECT id,
            plugin_id,
            name,
            is_exportable
            FROM claro_resource_type
        ");
        $this->addSql("
            DROP TABLE claro_resource_type
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type (
                id INTEGER NOT NULL,
                plugin_id INTEGER DEFAULT NULL,
                parent_id INTEGER DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                is_exportable BOOLEAN NOT NULL,
                class VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_AEC62693EC942BCF FOREIGN KEY (plugin_id)
                REFERENCES claro_plugin (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_AEC62693727ACA70 FOREIGN KEY (parent_id)
                REFERENCES claro_resource_type (id)
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_type (
                id, plugin_id, name, is_exportable
            )
            SELECT id,
            plugin_id,
            name,
            is_exportable
            FROM __temp__claro_resource_type
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_type
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_AEC626935E237E06 ON claro_resource_type (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693EC942BCF ON claro_resource_type (plugin_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693727ACA70 ON claro_resource_type (parent_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_5D9559DCB87FAB32
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
        $this->addSql("
            DROP INDEX IDX_F61948DE698D3548
        ");
        $this->addSql("
            DROP INDEX IDX_F61948DEA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_text_revision AS
            SELECT id,
            text_id,
            user_id,
            version,
            content
            FROM claro_text_revision
        ");
        $this->addSql("
            DROP TABLE claro_text_revision
        ");
        $this->addSql("
            CREATE TABLE claro_text_revision (
                id INTEGER NOT NULL,
                text_id INTEGER DEFAULT NULL,
                user_id INTEGER DEFAULT NULL,
                version INTEGER NOT NULL,
                content VARCHAR(255) NOT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_F61948DE698D3548 FOREIGN KEY (text_id)
                REFERENCES claro_text (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_F61948DEA76ED395 FOREIGN KEY (user_id)
                REFERENCES claro_user (id)
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_text_revision (
                id, text_id, user_id, version, content
            )
            SELECT id,
            text_id,
            user_id,
            version,
            content
            FROM __temp__claro_text_revision
        ");
        $this->addSql("
            DROP TABLE __temp__claro_text_revision
        ");
        $this->addSql("
            CREATE INDEX IDX_F61948DE698D3548 ON claro_text_revision (text_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F61948DEA76ED395 ON claro_text_revision (user_id)
        ");
    }
}
