<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/07/30 05:15:44
 */
class Version20130730171543 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                first_name VARCHAR(50) NOT NULL, 
                last_name VARCHAR(50) NOT NULL, 
                username VARCHAR(255) NOT NULL, 
                password VARCHAR(255) NOT NULL, 
                salt VARCHAR(255) NOT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                mail VARCHAR(255) NOT NULL, 
                administrative_code VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                reset_password VARCHAR(255) DEFAULT NULL, 
                hash_time INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852F85E0677 ON claro_user (username)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28525126AC48 ON claro_user (mail)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D285282D40A1F ON claro_user (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_user_group (
                user_id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                PRIMARY KEY(user_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_ED8B34C7A76ED395 ON claro_user_group (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_ED8B34C7FE54D947 ON claro_user_group (group_id)
        ");
        $this->addSql("
            CREATE TABLE claro_user_role (
                user_id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                PRIMARY KEY(user_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_797E43FFA76ED395 ON claro_user_role (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_797E43FFD60322AC ON claro_user_role (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_group (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX group_unique_name ON claro_group (name)
        ");
        $this->addSql("
            CREATE TABLE claro_group_role (
                group_id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                PRIMARY KEY(group_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1CBA5A40FE54D947 ON claro_group_role (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1CBA5A40D60322AC ON claro_group_role (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_role (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                translation_key VARCHAR(255) NOT NULL, 
                is_read_only BOOLEAN NOT NULL, 
                type INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3177747182D40A1F ON claro_role (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX role_unique_name ON claro_role (name)
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
                mime_type VARCHAR(255) DEFAULT NULL, 
                discr VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
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
            CREATE TABLE claro_workspace (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                is_public BOOLEAN DEFAULT NULL, 
                displayable BOOLEAN DEFAULT NULL, 
                guid VARCHAR(255) NOT NULL, 
                self_registration BOOLEAN DEFAULT NULL, 
                self_unregistration BOOLEAN DEFAULT NULL, 
                discr VARCHAR(255) NOT NULL, 
                lft INTEGER DEFAULT NULL, 
                lvl INTEGER DEFAULT NULL, 
                rgt INTEGER DEFAULT NULL, 
                root INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D902854577153098 ON claro_workspace (code)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D90285452B6FCFB2 ON claro_workspace (guid)
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545A76ED395 ON claro_workspace (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_aggregation (
                aggregator_workspace_id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                PRIMARY KEY(
                    aggregator_workspace_id, workspace_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D012AF0FA08DFE7A ON claro_workspace_aggregation (aggregator_workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D012AF0F82D40A1F ON claro_workspace_aggregation (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_user_message (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                message_id INTEGER NOT NULL, 
                is_removed BOOLEAN NOT NULL, 
                is_read BOOLEAN NOT NULL, 
                is_sent BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D48EA38AA76ED395 ON claro_user_message (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D48EA38A537A1329 ON claro_user_message (message_id)
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                tool_id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                display_order INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E82D40A1F ON claro_ordered_tool (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E8F7B22CC ON claro_ordered_tool (tool_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320EA76ED395 ON claro_ordered_tool (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_usr ON claro_ordered_tool (tool_id, workspace_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name)
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool_role (
                orderedtool_id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                PRIMARY KEY(orderedtool_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_9210497679732467 ON claro_ordered_tool_role (orderedtool_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_92104976D60322AC ON claro_ordered_tool_role (role_id)
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
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483D60322AC ON claro_resource_rights (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F48389329D25 ON claro_resource_rights (resource_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user ON claro_resource_rights (resource_id, role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_list_type_creation (
                right_id INTEGER NOT NULL, 
                resource_type_id INTEGER NOT NULL, 
                PRIMARY KEY(right_id, resource_type_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_84B4BEBA54976835 ON claro_list_type_creation (right_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_84B4BEBA98EC6B7B ON claro_list_type_creation (resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) DEFAULT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693EC942BCF ON claro_resource_type (plugin_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693727ACA70 ON claro_resource_type (parent_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX res_type_unique_name ON claro_resource_type (name)
        ");
        $this->addSql("
            CREATE TABLE claro_theme (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                path VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1D76301AEC942BCF ON claro_theme (plugin_id)
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
                PRIMARY KEY(id)
            )
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
            CREATE INDEX IDX_97FAB91F89329D25 ON claro_log (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F98EC6B7B ON claro_log (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FD60322AC ON claro_log (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_doer_platform_roles (
                log_id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                PRIMARY KEY(log_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_706568A5EA675D86 ON claro_log_doer_platform_roles (log_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_706568A5D60322AC ON claro_log_doer_platform_roles (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_doer_workspace_roles (
                log_id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                PRIMARY KEY(log_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8A8D2F47EA675D86 ON claro_log_doer_workspace_roles (log_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8A8D2F47D60322AC ON claro_log_doer_workspace_roles (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_desktop_widget_config (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                amount INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_4AE48D62A76ED395 ON claro_log_desktop_widget_config (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_workspace_widget_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                amount INTEGER NOT NULL, 
                resource_copy BOOLEAN NOT NULL, 
                resource_create BOOLEAN NOT NULL, 
                resource_shortcut BOOLEAN NOT NULL, 
                resource_read BOOLEAN NOT NULL, 
                ws_tool_read BOOLEAN NOT NULL, 
                resource_export BOOLEAN NOT NULL, 
                resource_update BOOLEAN NOT NULL, 
                resource_update_rename BOOLEAN NOT NULL, 
                resource_child_update BOOLEAN NOT NULL, 
                resource_delete BOOLEAN NOT NULL, 
                resource_move BOOLEAN NOT NULL, 
                ws_role_subscribe_user BOOLEAN NOT NULL, 
                ws_role_subscribe_group BOOLEAN NOT NULL, 
                ws_role_unsubscribe_user BOOLEAN NOT NULL, 
                ws_role_unsubscribe_group BOOLEAN NOT NULL, 
                ws_role_change_right BOOLEAN NOT NULL, 
                ws_role_create BOOLEAN NOT NULL, 
                ws_role_delete BOOLEAN NOT NULL, 
                ws_role_update BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D301C70782D40A1F ON claro_log_workspace_widget_config (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_hidden_workspace_widget_config (
                workspace_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(workspace_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_BC83196EA76ED395 ON claro_log_hidden_workspace_widget_config (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_template (
                id INTEGER NOT NULL, 
                hash VARCHAR(255) NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX template_unique_hash ON claro_workspace_template (hash)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_tag_hierarchy (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                tag_id INTEGER NOT NULL, 
                parent_id INTEGER NOT NULL, 
                level INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A46B159EA76ED395 ON claro_workspace_tag_hierarchy (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A46B159EBAD26311 ON claro_workspace_tag_hierarchy (tag_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A46B159E727ACA70 ON claro_workspace_tag_hierarchy (parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_rel_workspace_tag (
                id INTEGER NOT NULL, 
                workspace_id INTEGER NOT NULL, 
                tag_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7883931082D40A1F ON claro_rel_workspace_tag (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_78839310BAD26311 ON claro_rel_workspace_tag (tag_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX rel_workspace_tag_unique_combination ON claro_rel_workspace_tag (workspace_id, tag_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_tag (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EFA76ED395 ON claro_workspace_tag (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tag_unique_name_and_user ON claro_workspace_tag (user_id, name)
        ");
        $this->addSql("
            CREATE TABLE claro_plugin (
                id INTEGER NOT NULL, 
                vendor_name VARCHAR(50) NOT NULL, 
                short_name VARCHAR(50) NOT NULL, 
                has_options BOOLEAN NOT NULL, 
                icon VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX plugin_unique_name ON claro_plugin (vendor_name, short_name)
        ");
        $this->addSql("
            CREATE TABLE claro_message (
                id INTEGER NOT NULL, 
                sender_id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                object VARCHAR(255) NOT NULL, 
                content VARCHAR(1023) NOT NULL, 
                date DATETIME NOT NULL, 
                is_removed BOOLEAN NOT NULL, 
                lft INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                root INTEGER DEFAULT NULL, 
                sender_username VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD8F624B39D ON claro_message (sender_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD8727ACA70 ON claro_message (parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_event (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER NOT NULL, 
                title VARCHAR(50) NOT NULL, 
                start_date INTEGER DEFAULT NULL, 
                end_date INTEGER DEFAULT NULL, 
                description VARCHAR(255) DEFAULT NULL, 
                allday BOOLEAN DEFAULT NULL, 
                priority VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B1ADDDB582D40A1F ON claro_event (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B1ADDDB5A76ED395 ON claro_event (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_license (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                acronym VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_resource_activity (
                id INTEGER NOT NULL, 
                activity_id INTEGER NOT NULL, 
                resource_id INTEGER NOT NULL, 
                sequence_order INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7E81C06096 ON claro_resource_activity (activity_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7E89329D25 ON claro_resource_activity (resource_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_activity_unique_combination ON claro_resource_activity (activity_id, resource_id)
        ");
        $this->addSql("
            CREATE TABLE claro_link (
                id INTEGER NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_directory (
                id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_resource_icon (
                id INTEGER NOT NULL, 
                shortcut_id INTEGER DEFAULT NULL, 
                icon_location VARCHAR(255) DEFAULT NULL, 
                mimeType VARCHAR(255) NOT NULL, 
                is_shortcut BOOLEAN NOT NULL, 
                relative_url VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_478C586179F0D498 ON claro_resource_icon (shortcut_id)
        ");
        $this->addSql("
            CREATE TABLE claro_file (
                id INTEGER NOT NULL, 
                size INTEGER NOT NULL, 
                hash_name VARCHAR(36) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX file_unique_hashname ON claro_file (hash_name)
        ");
        $this->addSql("
            CREATE TABLE claro_text_revision (
                id INTEGER NOT NULL, 
                text_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                version INTEGER NOT NULL, 
                content VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F61948DE698D3548 ON claro_text_revision (text_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F61948DEA76ED395 ON claro_text_revision (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type_custom_action (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                \"action\" VARCHAR(255) DEFAULT NULL, 
                async BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_4A98967B98EC6B7B ON claro_resource_type_custom_action (resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_resource_shortcut (
                id INTEGER NOT NULL, 
                resource_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB889329D25 ON claro_resource_shortcut (resource_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity (
                id INTEGER NOT NULL, 
                instruction VARCHAR(255) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_text (
                id INTEGER NOT NULL, 
                version INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_tools (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                display_name VARCHAR(255) DEFAULT NULL, 
                class VARCHAR(255) NOT NULL, 
                is_workspace_required BOOLEAN NOT NULL, 
                is_desktop_required BOOLEAN NOT NULL, 
                is_displayable_in_workspace BOOLEAN NOT NULL, 
                is_displayable_in_desktop BOOLEAN NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                has_options BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_60F90965EC942BCF ON claro_tools (plugin_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tool_unique_name ON claro_tools (name)
        ");
        $this->addSql("
            CREATE TABLE claro_widget_display (
                id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                widget_id INTEGER NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                is_visible BOOLEAN NOT NULL, 
                is_desktop BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB3727ACA70 ON claro_widget_display (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB382D40A1F ON claro_widget_display (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB3A76ED395 ON claro_widget_display (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB3FBE885E2 ON claro_widget_display (widget_id)
        ");
        $this->addSql("
            CREATE TABLE claro_widget (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_configurable BOOLEAN NOT NULL, 
                icon VARCHAR(255) NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_76CA6C4FEC942BCF ON claro_widget (plugin_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_unique_name ON claro_widget (name)
        ");
        $this->addSql("
            CREATE TABLE claro_content (
                id INTEGER NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content CLOB DEFAULT NULL, 
                generated_content CLOB DEFAULT NULL, 
                created DATETIME NOT NULL, 
                modified DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_content2region (
                id INTEGER NOT NULL, 
                content_id INTEGER NOT NULL, 
                region_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                back_id INTEGER DEFAULT NULL, 
                size VARCHAR(30) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8D18942E84A0A3ED ON claro_content2region (content_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D18942E98260155 ON claro_content2region (region_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D18942EAA23F6C8 ON claro_content2region (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D18942EE9583FF0 ON claro_content2region (back_id)
        ");
        $this->addSql("
            CREATE TABLE claro_content2type (
                id INTEGER NOT NULL, 
                content_id INTEGER NOT NULL, 
                type_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                back_id INTEGER DEFAULT NULL, 
                size VARCHAR(30) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EF84A0A3ED ON claro_content2type (content_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EFC54C8C93 ON claro_content2type (type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EFAA23F6C8 ON claro_content2type (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EFE9583FF0 ON claro_content2type (back_id)
        ");
        $this->addSql("
            CREATE TABLE claro_subcontent (
                id INTEGER NOT NULL, 
                father_id INTEGER NOT NULL, 
                child_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                back_id INTEGER DEFAULT NULL, 
                size VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D72E133C2055B9A2 ON claro_subcontent (father_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D72E133CDD62C21B ON claro_subcontent (child_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D72E133CAA23F6C8 ON claro_subcontent (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D72E133CE9583FF0 ON claro_subcontent (back_id)
        ");
        $this->addSql("
            CREATE TABLE claro_region (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_type (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                max_content_page INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_user
        ");
        $this->addSql("
            DROP TABLE claro_user_group
        ");
        $this->addSql("
            DROP TABLE claro_user_role
        ");
        $this->addSql("
            DROP TABLE claro_group
        ");
        $this->addSql("
            DROP TABLE claro_group_role
        ");
        $this->addSql("
            DROP TABLE claro_role
        ");
        $this->addSql("
            DROP TABLE claro_resource
        ");
        $this->addSql("
            DROP TABLE claro_workspace
        ");
        $this->addSql("
            DROP TABLE claro_workspace_aggregation
        ");
        $this->addSql("
            DROP TABLE claro_user_message
        ");
        $this->addSql("
            DROP TABLE claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE claro_ordered_tool_role
        ");
        $this->addSql("
            DROP TABLE claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE claro_list_type_creation
        ");
        $this->addSql("
            DROP TABLE claro_resource_type
        ");
        $this->addSql("
            DROP TABLE claro_theme
        ");
        $this->addSql("
            DROP TABLE claro_log
        ");
        $this->addSql("
            DROP TABLE claro_log_doer_platform_roles
        ");
        $this->addSql("
            DROP TABLE claro_log_doer_workspace_roles
        ");
        $this->addSql("
            DROP TABLE claro_log_desktop_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_workspace_template
        ");
        $this->addSql("
            DROP TABLE claro_workspace_tag_hierarchy
        ");
        $this->addSql("
            DROP TABLE claro_rel_workspace_tag
        ");
        $this->addSql("
            DROP TABLE claro_workspace_tag
        ");
        $this->addSql("
            DROP TABLE claro_plugin
        ");
        $this->addSql("
            DROP TABLE claro_message
        ");
        $this->addSql("
            DROP TABLE claro_event
        ");
        $this->addSql("
            DROP TABLE claro_license
        ");
        $this->addSql("
            DROP TABLE claro_resource_activity
        ");
        $this->addSql("
            DROP TABLE claro_link
        ");
        $this->addSql("
            DROP TABLE claro_directory
        ");
        $this->addSql("
            DROP TABLE claro_resource_icon
        ");
        $this->addSql("
            DROP TABLE claro_file
        ");
        $this->addSql("
            DROP TABLE claro_text_revision
        ");
        $this->addSql("
            DROP TABLE claro_resource_type_custom_action
        ");
        $this->addSql("
            DROP TABLE claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE claro_activity
        ");
        $this->addSql("
            DROP TABLE claro_text
        ");
        $this->addSql("
            DROP TABLE claro_tools
        ");
        $this->addSql("
            DROP TABLE claro_widget_display
        ");
        $this->addSql("
            DROP TABLE claro_widget
        ");
        $this->addSql("
            DROP TABLE claro_content
        ");
        $this->addSql("
            DROP TABLE claro_content2region
        ");
        $this->addSql("
            DROP TABLE claro_content2type
        ");
        $this->addSql("
            DROP TABLE claro_subcontent
        ");
        $this->addSql("
            DROP TABLE claro_region
        ");
        $this->addSql("
            DROP TABLE claro_type
        ");
    }
}