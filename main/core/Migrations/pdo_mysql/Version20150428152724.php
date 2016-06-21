<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/28 03:27:24
 */
class Version20150428152724 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_workspace (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                code VARCHAR(255) NOT NULL, 
                maxStorageSize VARCHAR(255) NOT NULL, 
                maxUploadResources INT NOT NULL, 
                maxUsers INT NOT NULL, 
                displayable TINYINT(1) NOT NULL, 
                guid VARCHAR(255) NOT NULL, 
                self_registration TINYINT(1) NOT NULL, 
                registration_validation TINYINT(1) NOT NULL, 
                self_unregistration TINYINT(1) NOT NULL, 
                creation_date INT DEFAULT NULL, 
                is_personal TINYINT(1) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                is_access_date TINYINT(1) NOT NULL, 
                workspace_type INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_D902854577153098 (code), 
                UNIQUE INDEX UNIQ_D90285452B6FCFB2 (guid), 
                INDEX IDX_D9028545A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                options_id INT DEFAULT NULL, 
                first_name VARCHAR(50) NOT NULL, 
                last_name VARCHAR(50) NOT NULL, 
                username VARCHAR(255) NOT NULL, 
                password VARCHAR(255) NOT NULL, 
                locale VARCHAR(255) DEFAULT NULL, 
                salt VARCHAR(255) NOT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                mail VARCHAR(255) NOT NULL, 
                administrative_code VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                initialization_date DATETIME DEFAULT NULL, 
                reset_password VARCHAR(255) DEFAULT NULL, 
                hash_time INT DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                hasAcceptedTerms TINYINT(1) DEFAULT NULL, 
                is_enabled TINYINT(1) NOT NULL, 
                is_mail_notified TINYINT(1) NOT NULL, 
                last_uri VARCHAR(255) DEFAULT NULL, 
                public_url VARCHAR(255) DEFAULT NULL, 
                has_tuned_public_url TINYINT(1) NOT NULL, 
                expiration_date DATETIME DEFAULT NULL, 
                authentication VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_EB8D2852F85E0677 (username), 
                UNIQUE INDEX UNIQ_EB8D28525126AC48 (mail), 
                UNIQUE INDEX UNIQ_EB8D2852181F3A64 (public_url), 
                UNIQUE INDEX UNIQ_EB8D285282D40A1F (workspace_id), 
                UNIQUE INDEX UNIQ_EB8D28523ADB05F1 (options_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_group (
                user_id INT NOT NULL, 
                group_id INT NOT NULL, 
                INDEX IDX_ED8B34C7A76ED395 (user_id), 
                INDEX IDX_ED8B34C7FE54D947 (group_id), 
                PRIMARY KEY(user_id, group_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_role (
                user_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_797E43FFA76ED395 (user_id), 
                INDEX IDX_797E43FFD60322AC (role_id), 
                PRIMARY KEY(user_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_model_user (
                user_id INT NOT NULL, 
                workspacemodel_id INT NOT NULL, 
                INDEX IDX_5318388FA76ED395 (user_id), 
                INDEX IDX_5318388FD500BD91 (workspacemodel_id), 
                PRIMARY KEY(user_id, workspacemodel_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_group (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                UNIQUE INDEX group_unique_name (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_group_role (
                group_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_1CBA5A40FE54D947 (group_id), 
                INDEX IDX_1CBA5A40D60322AC (role_id), 
                PRIMARY KEY(group_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_model_group (
                group_id INT NOT NULL, 
                workspacemodel_id INT NOT NULL, 
                INDEX IDX_1F19A8AEFE54D947 (group_id), 
                INDEX IDX_1F19A8AED500BD91 (workspacemodel_id), 
                PRIMARY KEY(group_id, workspacemodel_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_role (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                translation_key VARCHAR(255) NOT NULL, 
                is_read_only TINYINT(1) NOT NULL, 
                type INT NOT NULL, 
                maxUsers INT DEFAULT NULL, 
                personal_workspace_creation_enabled TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_317774715E237E06 (name), 
                INDEX IDX_3177747182D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT NOT NULL, 
                creator_id INT NOT NULL, 
                icon_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                workspace_id INT NOT NULL, 
                license VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INT DEFAULT NULL, 
                path VARCHAR(3000) DEFAULT NULL, 
                value INT DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                class VARCHAR(256) NOT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                published TINYINT(1) DEFAULT '1' NOT NULL, 
                author VARCHAR(255) DEFAULT NULL, 
                active TINYINT(1) DEFAULT '1' NOT NULL, 
                INDEX IDX_A76799FF98EC6B7B (resource_type_id), 
                INDEX IDX_A76799FF61220EA6 (creator_id), 
                INDEX IDX_A76799FF54B9D732 (icon_id), 
                INDEX IDX_A76799FF727ACA70 (parent_id), 
                INDEX IDX_A76799FF82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_ordered_tool (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                tool_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                display_order INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_visible_in_desktop TINYINT(1) NOT NULL, 
                ordered_tool_type INT NOT NULL, 
                is_locked TINYINT(1) NOT NULL, 
                INDEX IDX_6CF1320E82D40A1F (workspace_id), 
                INDEX IDX_6CF1320E8F7B22CC (tool_id), 
                INDEX IDX_6CF1320EA76ED395 (user_id), 
                UNIQUE INDEX ordered_tool_unique_tool_user_type (
                    tool_id, user_id, ordered_tool_type
                ), 
                UNIQUE INDEX ordered_tool_unique_tool_ws_type (
                    tool_id, workspace_id, ordered_tool_type
                ), 
                UNIQUE INDEX ordered_tool_unique_name_by_workspace (workspace_id, name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_field_facet_value (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                stringValue VARCHAR(255) DEFAULT NULL, 
                floatValue DOUBLE PRECISION DEFAULT NULL, 
                dateValue DATETIME DEFAULT NULL, 
                fieldFacet_id INT NOT NULL, 
                INDEX IDX_35307C0AA76ED395 (user_id), 
                INDEX IDX_35307C0A9F9239AF (fieldFacet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_model (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_536FFC4C5E237E06 (name), 
                INDEX IDX_536FFC4C82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_model_home_tab (
                workspacemodel_id INT NOT NULL, 
                hometab_id INT NOT NULL, 
                INDEX IDX_A8E0CB1BD500BD91 (workspacemodel_id), 
                INDEX IDX_A8E0CB1BCCE862F (hometab_id), 
                PRIMARY KEY(workspacemodel_id, hometab_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_options (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                desktop_background_color VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B2066972A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_api_client (
                id INT AUTO_INCREMENT NOT NULL, 
                random_id VARCHAR(255) NOT NULL, 
                redirect_uris LONGTEXT NOT NULL COMMENT '(DC2Type:array)', 
                secret VARCHAR(255) NOT NULL, 
                allowed_grant_types LONGTEXT NOT NULL COMMENT '(DC2Type:array)', 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_api_access_token (
                id INT AUTO_INCREMENT NOT NULL, 
                client_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                token VARCHAR(255) NOT NULL, 
                expires_at INT DEFAULT NULL, 
                scope VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_CE948285F37A13B (token), 
                INDEX IDX_CE9482819EB6921 (client_id), 
                INDEX IDX_CE94828A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_api_auth_code (
                id INT AUTO_INCREMENT NOT NULL, 
                client_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                token VARCHAR(255) NOT NULL, 
                redirect_uri LONGTEXT NOT NULL, 
                expires_at INT DEFAULT NULL, 
                scope VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_9DFA4575F37A13B (token), 
                INDEX IDX_9DFA45719EB6921 (client_id), 
                INDEX IDX_9DFA457A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_api_refresh_token (
                id INT AUTO_INCREMENT NOT NULL, 
                client_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                token VARCHAR(255) NOT NULL, 
                expires_at INT DEFAULT NULL, 
                scope VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B1292B905F37A13B (token), 
                INDEX IDX_B1292B9019EB6921 (client_id), 
                INDEX IDX_B1292B90A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_resource_mask_decoder (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT NOT NULL, 
                value INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_39D93F4298EC6B7B (resource_type_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_resource_type (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_exportable TINYINT(1) NOT NULL, 
                defaultMask INT NOT NULL, 
                UNIQUE INDEX UNIQ_AEC626935E237E06 (name), 
                INDEX IDX_AEC62693EC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_menu_action (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                async TINYINT(1) DEFAULT NULL, 
                is_custom TINYINT(1) NOT NULL, 
                is_form TINYINT(1) NOT NULL, 
                value VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_1F57E52B98EC6B7B (resource_type_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                position INT NOT NULL, 
                isVisibleByOwner TINYINT(1) NOT NULL, 
                forceCreationForm TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_facet_role (
                facet_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_CDD5845DFC889F24 (facet_id), 
                INDEX IDX_CDD5845DD60322AC (role_id), 
                PRIMARY KEY(facet_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_field_facet_role (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                canOpen TINYINT(1) NOT NULL, 
                canEdit TINYINT(1) NOT NULL, 
                fieldFacet_id INT NOT NULL, 
                INDEX IDX_12F52A52D60322AC (role_id), 
                INDEX IDX_12F52A529F9239AF (fieldFacet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_general_facet_preference (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                baseData TINYINT(1) NOT NULL, 
                mail TINYINT(1) NOT NULL, 
                phone TINYINT(1) NOT NULL, 
                sendMail TINYINT(1) NOT NULL, 
                sendMessage TINYINT(1) NOT NULL, 
                INDEX IDX_38AACF88D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_admin_tools (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_C10C14EC5E237E06 (name), 
                INDEX IDX_C10C14ECEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_admin_tool_role (
                admintool_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_940800692B80F4B6 (admintool_id), 
                INDEX IDX_94080069D60322AC (role_id), 
                PRIMARY KEY(admintool_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_resource_rights (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                mask INT NOT NULL, 
                resourceNode_id INT NOT NULL, 
                INDEX IDX_3848F483D60322AC (role_id), 
                INDEX IDX_3848F483B87FAB32 (resourceNode_id), 
                UNIQUE INDEX resource_rights_unique_resource_role (resourceNode_id, role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_list_type_creation (
                resource_rights_id INT NOT NULL, 
                resource_type_id INT NOT NULL, 
                INDEX IDX_84B4BEBA195FBDF1 (resource_rights_id), 
                INDEX IDX_84B4BEBA98EC6B7B (resource_type_id), 
                PRIMARY KEY(
                    resource_rights_id, resource_type_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_tool_rights (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                ordered_tool_id INT NOT NULL, 
                mask INT NOT NULL, 
                INDEX IDX_EFEDEC7ED60322AC (role_id), 
                INDEX IDX_EFEDEC7EBAC1B1D7 (ordered_tool_id), 
                UNIQUE INDEX tool_rights_unique_ordered_tool_role (ordered_tool_id, role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_personnal_workspace_tool_config (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                tool_id INT NOT NULL, 
                mask INT NOT NULL, 
                INDEX IDX_7A4A6A64D60322AC (role_id), 
                INDEX IDX_7A4A6A648F7B22CC (tool_id), 
                UNIQUE INDEX pws_unique_tool_config (tool_id, role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_personal_workspace_resource_rights_management_access (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                is_accessible TINYINT(1) NOT NULL, 
                INDEX IDX_A3AE069AD60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_profile_property (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT DEFAULT NULL, 
                is_editable TINYINT(1) NOT NULL, 
                property VARCHAR(256) NOT NULL, 
                INDEX IDX_C2B93182D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_resource_icon (
                id INT AUTO_INCREMENT NOT NULL, 
                shortcut_id INT DEFAULT NULL, 
                mimeType VARCHAR(255) NOT NULL, 
                is_shortcut TINYINT(1) NOT NULL, 
                relative_url VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_478C586179F0D498 (shortcut_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_resource_shortcut (
                id INT AUTO_INCREMENT NOT NULL, 
                target_id INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                INDEX IDX_5E7F4AB8158E0B66 (target_id), 
                UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_log (
                id INT AUTO_INCREMENT NOT NULL, 
                doer_id INT DEFAULT NULL, 
                receiver_id INT DEFAULT NULL, 
                receiver_group_id INT DEFAULT NULL, 
                owner_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                resource_type_id INT DEFAULT NULL, 
                role_id INT DEFAULT NULL, 
                action VARCHAR(255) NOT NULL, 
                date_log DATETIME NOT NULL, 
                short_date_log DATE NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                doer_type VARCHAR(255) NOT NULL, 
                doer_ip VARCHAR(255) DEFAULT NULL, 
                doer_session_id VARCHAR(255) DEFAULT NULL, 
                tool_name VARCHAR(255) DEFAULT NULL, 
                is_displayed_in_admin TINYINT(1) NOT NULL, 
                is_displayed_in_workspace TINYINT(1) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                INDEX IDX_97FAB91F12D3860F (doer_id), 
                INDEX IDX_97FAB91FCD53EDB6 (receiver_id), 
                INDEX IDX_97FAB91FC6F122B2 (receiver_group_id), 
                INDEX IDX_97FAB91F7E3C61F9 (owner_id), 
                INDEX IDX_97FAB91F82D40A1F (workspace_id), 
                INDEX IDX_97FAB91FB87FAB32 (resourceNode_id), 
                INDEX IDX_97FAB91F98EC6B7B (resource_type_id), 
                INDEX IDX_97FAB91FD60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_log_doer_platform_roles (
                log_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_706568A5EA675D86 (log_id), 
                INDEX IDX_706568A5D60322AC (role_id), 
                PRIMARY KEY(log_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_log_doer_workspace_roles (
                log_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_8A8D2F47EA675D86 (log_id), 
                INDEX IDX_8A8D2F47D60322AC (role_id), 
                PRIMARY KEY(log_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_plugin (
                id INT AUTO_INCREMENT NOT NULL, 
                vendor_name VARCHAR(50) NOT NULL, 
                short_name VARCHAR(50) NOT NULL, 
                has_options TINYINT(1) NOT NULL, 
                UNIQUE INDEX plugin_unique_name (vendor_name, short_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_directory (
                id INT AUTO_INCREMENT NOT NULL, 
                is_upload_destination TINYINT(1) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_12EEC186B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_home_tab (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                icon VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_A9744CCEA76ED395 (user_id), 
                INDEX IDX_A9744CCE82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_home_tab_roles (
                hometab_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_B81359F3CCE862F (hometab_id), 
                INDEX IDX_B81359F3D60322AC (role_id), 
                PRIMARY KEY(hometab_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_widget_home_tab_config (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_instance_id INT DEFAULT NULL, 
                home_tab_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                widget_order INT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                is_visible TINYINT(1) NOT NULL, 
                is_locked TINYINT(1) NOT NULL, 
                INDEX IDX_D48CC23E44BF891 (widget_instance_id), 
                INDEX IDX_D48CC23E7D08FA9E (home_tab_id), 
                INDEX IDX_D48CC23EA76ED395 (user_id), 
                INDEX IDX_D48CC23E82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_home_tab_config (
                id INT AUTO_INCREMENT NOT NULL, 
                home_tab_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                is_visible TINYINT(1) NOT NULL, 
                is_locked TINYINT(1) NOT NULL, 
                tab_order INT NOT NULL, 
                INDEX IDX_F530F6BE7D08FA9E (home_tab_id), 
                INDEX IDX_F530F6BEA76ED395 (user_id), 
                INDEX IDX_F530F6BE82D40A1F (workspace_id), 
                UNIQUE INDEX home_tab_config_unique_home_tab_user_workspace_type (
                    home_tab_id, user_id, workspace_id, 
                    type
                ), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_widget_instance (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                widget_id INT NOT NULL, 
                is_admin TINYINT(1) NOT NULL, 
                is_desktop TINYINT(1) NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                icon VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_5F89A38582D40A1F (workspace_id), 
                INDEX IDX_5F89A385A76ED395 (user_id), 
                INDEX IDX_5F89A385FBE885E2 (widget_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_tools (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                display_name VARCHAR(255) DEFAULT NULL, 
                class VARCHAR(255) NOT NULL, 
                is_workspace_required TINYINT(1) NOT NULL, 
                is_desktop_required TINYINT(1) NOT NULL, 
                is_displayable_in_workspace TINYINT(1) NOT NULL, 
                is_displayable_in_desktop TINYINT(1) NOT NULL, 
                is_exportable TINYINT(1) NOT NULL, 
                is_configurable_in_workspace TINYINT(1) NOT NULL, 
                is_configurable_in_desktop TINYINT(1) NOT NULL, 
                is_locked_for_admin TINYINT(1) NOT NULL, 
                is_anonymous_excluded TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_60F909655E237E06 (name), 
                INDEX IDX_60F90965EC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_favourite (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_711A30B82D40A1F (workspace_id), 
                INDEX IDX_711A30BA76ED395 (user_id), 
                UNIQUE INDEX workspace_favourite_unique_combination (workspace_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_content (
                id INT AUTO_INCREMENT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                type VARCHAR(255) DEFAULT NULL, 
                created DATETIME NOT NULL, 
                modified DATETIME NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_content_translation (
                id INT AUTO_INCREMENT NOT NULL, 
                locale VARCHAR(8) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                field VARCHAR(32) NOT NULL, 
                foreign_key VARCHAR(64) NOT NULL, 
                content LONGTEXT DEFAULT NULL, 
                INDEX content_translation_idx (
                    locale, object_class, field, foreign_key
                ), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_log_hidden_workspace_widget_config (
                workspace_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_BC83196EA76ED395 (user_id), 
                PRIMARY KEY(workspace_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_log_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                amount INT NOT NULL, 
                restrictions LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', 
                widgetInstance_id INT DEFAULT NULL, 
                INDEX IDX_C16334B2AB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_security_token (
                id INT AUTO_INCREMENT NOT NULL, 
                client_name VARCHAR(255) NOT NULL, 
                token VARCHAR(255) NOT NULL, 
                client_ip VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_B3A67A408FBFBD64 (client_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_theme (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                path VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_1D76301AEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_subcontent (
                id INT AUTO_INCREMENT NOT NULL, 
                father_id INT NOT NULL, 
                child_id INT NOT NULL, 
                next_id INT DEFAULT NULL, 
                back_id INT DEFAULT NULL, 
                size VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_D72E133C2055B9A2 (father_id), 
                INDEX IDX_D72E133CDD62C21B (child_id), 
                INDEX IDX_D72E133CAA23F6C8 (next_id), 
                INDEX IDX_D72E133CE9583FF0 (back_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_content2type (
                id INT AUTO_INCREMENT NOT NULL, 
                content_id INT NOT NULL, 
                type_id INT NOT NULL, 
                next_id INT DEFAULT NULL, 
                back_id INT DEFAULT NULL, 
                size VARCHAR(30) NOT NULL, 
                collapse TINYINT(1) NOT NULL, 
                INDEX IDX_1A2084EF84A0A3ED (content_id), 
                INDEX IDX_1A2084EFC54C8C93 (type_id), 
                INDEX IDX_1A2084EFAA23F6C8 (next_id), 
                INDEX IDX_1A2084EFE9583FF0 (back_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_type (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                max_content_page INT NOT NULL, 
                publish TINYINT(1) DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_content2region (
                id INT AUTO_INCREMENT NOT NULL, 
                content_id INT NOT NULL, 
                region_id INT NOT NULL, 
                next_id INT DEFAULT NULL, 
                back_id INT DEFAULT NULL, 
                size VARCHAR(30) NOT NULL, 
                INDEX IDX_8D18942E84A0A3ED (content_id), 
                INDEX IDX_8D18942E98260155 (region_id), 
                INDEX IDX_8D18942EAA23F6C8 (next_id), 
                INDEX IDX_8D18942EE9583FF0 (back_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_region (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_widget_display_config (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                widget_instance_id INT NOT NULL, 
                row_position INT NOT NULL, 
                column_position INT NOT NULL, 
                width INT DEFAULT 4 NOT NULL, 
                height INT DEFAULT 3 NOT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_EBBE497282D40A1F (workspace_id), 
                INDEX IDX_EBBE4972A76ED395 (user_id), 
                INDEX IDX_EBBE497244BF891 (widget_instance_id), 
                UNIQUE INDEX widget_display_config_unique_user (widget_instance_id, user_id), 
                UNIQUE INDEX widget_display_config_unique_workspace (
                    widget_instance_id, workspace_id
                ), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_widget (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_configurable TINYINT(1) NOT NULL, 
                is_exportable TINYINT(1) NOT NULL, 
                is_displayable_in_workspace TINYINT(1) NOT NULL, 
                is_displayable_in_desktop TINYINT(1) NOT NULL, 
                default_width INT DEFAULT 4 NOT NULL, 
                default_height INT DEFAULT 3 NOT NULL, 
                UNIQUE INDEX UNIQ_76CA6C4F5E237E06 (name), 
                INDEX IDX_76CA6C4FEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_simple_text_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                content LONGTEXT NOT NULL, 
                widgetInstance_id INT DEFAULT NULL, 
                INDEX IDX_C389EBCCAB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_activity_rule (
                id INT AUTO_INCREMENT NOT NULL, 
                activity_parameters_id INT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                result_visible TINYINT(1) DEFAULT NULL, 
                occurrence SMALLINT NOT NULL, 
                action VARCHAR(255) NOT NULL, 
                result VARCHAR(255) DEFAULT NULL, 
                resultMax VARCHAR(255) DEFAULT NULL, 
                resultComparison SMALLINT DEFAULT NULL, 
                userType SMALLINT NOT NULL, 
                active_from DATETIME DEFAULT NULL, 
                active_until DATETIME DEFAULT NULL, 
                INDEX IDX_6824A65E896F55DB (activity_parameters_id), 
                INDEX IDX_6824A65E89329D25 (resource_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                activity_parameters_id INT NOT NULL, 
                log_id INT DEFAULT NULL, 
                lastest_evaluation_date DATETIME DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                total_duration INT DEFAULT NULL, 
                attempts_count INT DEFAULT NULL, 
                INDEX IDX_F75EC869A76ED395 (user_id), 
                INDEX IDX_F75EC869896F55DB (activity_parameters_id), 
                INDEX IDX_F75EC869EA675D86 (log_id), 
                UNIQUE INDEX user_activity_unique_evaluation (user_id, activity_parameters_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_activity_past_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                activity_parameters_id INT DEFAULT NULL, 
                log_id INT DEFAULT NULL, 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                INDEX IDX_F1A76182A76ED395 (user_id), 
                INDEX IDX_F1A76182896F55DB (activity_parameters_id), 
                INDEX IDX_F1A76182EA675D86 (log_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_activity_rule_action (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT DEFAULT NULL, 
                log_action VARCHAR(255) NOT NULL, 
                INDEX IDX_C8835D2098EC6B7B (resource_type_id), 
                UNIQUE INDEX activity_rule_unique_action_resource_type (log_action, resource_type_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_activity_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                activity_id INT DEFAULT NULL, 
                max_duration INT DEFAULT NULL, 
                max_attempts INT DEFAULT NULL, 
                who VARCHAR(255) DEFAULT NULL, 
                activity_where VARCHAR(255) DEFAULT NULL, 
                with_tutor TINYINT(1) DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_E2EE25E281C06096 (activity_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_activity_secondary_resources (
                activityparameters_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                INDEX IDX_713242A7DB5E3CF7 (activityparameters_id), 
                INDEX IDX_713242A777C292AE (resourcenode_id), 
                PRIMARY KEY(
                    activityparameters_id, resourcenode_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_session (
                session_id VARCHAR(255) NOT NULL, 
                session_data LONGTEXT NOT NULL, 
                session_time INT NOT NULL, 
                PRIMARY KEY(session_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_registration_queue (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                user_id INT NOT NULL, 
                workspace_id INT NOT NULL, 
                INDEX IDX_F461C538D60322AC (role_id), 
                INDEX IDX_F461C538A76ED395 (user_id), 
                INDEX IDX_F461C53882D40A1F (workspace_id), 
                UNIQUE INDEX user_role_unique (role_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_tag (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_C8EFD7EFA76ED395 (user_id), 
                INDEX IDX_C8EFD7EF82D40A1F (workspace_id), 
                UNIQUE INDEX tag_unique_name_and_user (user_id, name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_rel_workspace_tag (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                tag_id INT NOT NULL, 
                INDEX IDX_7883931082D40A1F (workspace_id), 
                INDEX IDX_78839310BAD26311 (tag_id), 
                UNIQUE INDEX rel_workspace_tag_unique_combination (workspace_id, tag_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_tag_hierarchy (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                tag_id INT NOT NULL, 
                parent_id INT NOT NULL, 
                level INT NOT NULL, 
                INDEX IDX_A46B159EA76ED395 (user_id), 
                INDEX IDX_A46B159EBAD26311 (tag_id), 
                INDEX IDX_A46B159E727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_bundle (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(100) NOT NULL, 
                version VARCHAR(50) NOT NULL, 
                type VARCHAR(50) NOT NULL, 
                authors LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                description LONGTEXT DEFAULT NULL, 
                targetDir LONGTEXT NOT NULL, 
                basePath LONGTEXT NOT NULL, 
                license LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                isInstalled TINYINT(1) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_workspace_model_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_node_id INT NOT NULL, 
                model_id INT NOT NULL, 
                isCopy TINYINT(1) NOT NULL, 
                INDEX IDX_F5D706351BAD783F (resource_node_id), 
                INDEX IDX_F5D706357975B7E7 (model_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_tool_mask_decoder (
                id INT AUTO_INCREMENT NOT NULL, 
                tool_id INT NOT NULL, 
                value INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                granted_icon_class VARCHAR(255) NOT NULL, 
                denied_icon_class VARCHAR(255) NOT NULL, 
                INDEX IDX_323623448F7B22CC (tool_id), 
                UNIQUE INDEX tool_mask_decoder_unique_tool_and_name (tool_id, name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_file (
                id INT AUTO_INCREMENT NOT NULL, 
                size INT NOT NULL, 
                hash_name VARCHAR(255) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_EA81C80BE1F029B6 (hash_name), 
                UNIQUE INDEX UNIQ_EA81C80BB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_text_revision (
                id INT AUTO_INCREMENT NOT NULL, 
                text_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                version INT NOT NULL, 
                content LONGTEXT NOT NULL, 
                INDEX IDX_F61948DE698D3548 (text_id), 
                INDEX IDX_F61948DEA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_text (
                id INT AUTO_INCREMENT NOT NULL, 
                version INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5D9559DCB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_activity (
                id INT AUTO_INCREMENT NOT NULL, 
                parameters_id INT DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT NOT NULL, 
                primaryResource_id INT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                INDEX IDX_E4A67CAC52410EEC (primaryResource_id), 
                UNIQUE INDEX UNIQ_E4A67CAC88BD9C1F (parameters_id), 
                UNIQUE INDEX UNIQ_E4A67CACB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_panel_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                facet_id INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                position INT NOT NULL, 
                isDefaultCollapsed TINYINT(1) NOT NULL, 
                INDEX IDX_DA3985FFC889F24 (facet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_field_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                type INT NOT NULL, 
                position INT NOT NULL, 
                isVisibleByOwner TINYINT(1) NOT NULL, 
                isEditableByOwner TINYINT(1) NOT NULL, 
                panelFacet_id INT NOT NULL, 
                INDEX IDX_F6C21DB2E99038C0 (panelFacet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D9028545A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D28523ADB05F1 FOREIGN KEY (options_id) 
            REFERENCES claro_user_options (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_user_group 
            ADD CONSTRAINT FK_ED8B34C7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_group 
            ADD CONSTRAINT FK_ED8B34C7FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_role 
            ADD CONSTRAINT FK_797E43FFA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_role 
            ADD CONSTRAINT FK_797E43FFD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_user 
            ADD CONSTRAINT FK_5318388FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_user 
            ADD CONSTRAINT FK_5318388FD500BD91 FOREIGN KEY (workspacemodel_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_group_role 
            ADD CONSTRAINT FK_1CBA5A40FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_group_role 
            ADD CONSTRAINT FK_1CBA5A40D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_group 
            ADD CONSTRAINT FK_1F19A8AEFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_group 
            ADD CONSTRAINT FK_1F19A8AED500BD91 FOREIGN KEY (workspacemodel_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_role 
            ADD CONSTRAINT FK_3177747182D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF98EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD CONSTRAINT FK_6CF1320E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD CONSTRAINT FK_6CF1320E8F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD CONSTRAINT FK_6CF1320EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            ADD CONSTRAINT FK_35307C0AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            ADD CONSTRAINT FK_35307C0A9F9239AF FOREIGN KEY (fieldFacet_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model 
            ADD CONSTRAINT FK_536FFC4C82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_home_tab 
            ADD CONSTRAINT FK_A8E0CB1BD500BD91 FOREIGN KEY (workspacemodel_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_home_tab 
            ADD CONSTRAINT FK_A8E0CB1BCCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_options 
            ADD CONSTRAINT FK_B2066972A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_api_access_token 
            ADD CONSTRAINT FK_CE9482819EB6921 FOREIGN KEY (client_id) 
            REFERENCES claro_api_client (id)
        ');
        $this->addSql('
            ALTER TABLE claro_api_access_token 
            ADD CONSTRAINT FK_CE94828A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_api_auth_code 
            ADD CONSTRAINT FK_9DFA45719EB6921 FOREIGN KEY (client_id) 
            REFERENCES claro_api_client (id)
        ');
        $this->addSql('
            ALTER TABLE claro_api_auth_code 
            ADD CONSTRAINT FK_9DFA457A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_api_refresh_token 
            ADD CONSTRAINT FK_B1292B9019EB6921 FOREIGN KEY (client_id) 
            REFERENCES claro_api_client (id)
        ');
        $this->addSql('
            ALTER TABLE claro_api_refresh_token 
            ADD CONSTRAINT FK_B1292B90A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_resource_mask_decoder 
            ADD CONSTRAINT FK_39D93F4298EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_type 
            ADD CONSTRAINT FK_AEC62693EC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action 
            ADD CONSTRAINT FK_1F57E52B98EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_facet_role 
            ADD CONSTRAINT FK_CDD5845DFC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_facet_role 
            ADD CONSTRAINT FK_CDD5845DD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_role 
            ADD CONSTRAINT FK_12F52A52D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_role 
            ADD CONSTRAINT FK_12F52A529F9239AF FOREIGN KEY (fieldFacet_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_general_facet_preference 
            ADD CONSTRAINT FK_38AACF88D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tools 
            ADD CONSTRAINT FK_C10C14ECEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tool_role 
            ADD CONSTRAINT FK_940800692B80F4B6 FOREIGN KEY (admintool_id) 
            REFERENCES claro_admin_tools (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tool_role 
            ADD CONSTRAINT FK_94080069D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_rights 
            ADD CONSTRAINT FK_3848F483D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_rights 
            ADD CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_list_type_creation 
            ADD CONSTRAINT FK_84B4BEBA195FBDF1 FOREIGN KEY (resource_rights_id) 
            REFERENCES claro_resource_rights (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_list_type_creation 
            ADD CONSTRAINT FK_84B4BEBA98EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_tool_rights 
            ADD CONSTRAINT FK_EFEDEC7ED60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_tool_rights 
            ADD CONSTRAINT FK_EFEDEC7EBAC1B1D7 FOREIGN KEY (ordered_tool_id) 
            REFERENCES claro_ordered_tool (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_personnal_workspace_tool_config 
            ADD CONSTRAINT FK_7A4A6A64D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_personnal_workspace_tool_config 
            ADD CONSTRAINT FK_7A4A6A648F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_personal_workspace_resource_rights_management_access 
            ADD CONSTRAINT FK_A3AE069AD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_profile_property 
            ADD CONSTRAINT FK_C2B93182D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_icon 
            ADD CONSTRAINT FK_478C586179F0D498 FOREIGN KEY (shortcut_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91F12D3860F FOREIGN KEY (doer_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91FCD53EDB6 FOREIGN KEY (receiver_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91FC6F122B2 FOREIGN KEY (receiver_group_id) 
            REFERENCES claro_group (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91F7E3C61F9 FOREIGN KEY (owner_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91F82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91F98EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            ADD CONSTRAINT FK_97FAB91FD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_doer_platform_roles 
            ADD CONSTRAINT FK_706568A5EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_doer_platform_roles 
            ADD CONSTRAINT FK_706568A5D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_doer_workspace_roles 
            ADD CONSTRAINT FK_8A8D2F47EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_doer_workspace_roles 
            ADD CONSTRAINT FK_8A8D2F47D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD CONSTRAINT FK_A9744CCEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD CONSTRAINT FK_A9744CCE82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F3CCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F3D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23E44BF891 FOREIGN KEY (widget_instance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23E7D08FA9E FOREIGN KEY (home_tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BE7D08FA9E FOREIGN KEY (home_tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BE82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A38582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES claro_widget (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_tools 
            ADD CONSTRAINT FK_60F90965EC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30B82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            ADD CONSTRAINT FK_711A30BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            ADD CONSTRAINT FK_BC83196EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_widget_config 
            ADD CONSTRAINT FK_C16334B2AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_theme 
            ADD CONSTRAINT FK_1D76301AEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_subcontent 
            ADD CONSTRAINT FK_D72E133C2055B9A2 FOREIGN KEY (father_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_subcontent 
            ADD CONSTRAINT FK_D72E133CDD62C21B FOREIGN KEY (child_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_subcontent 
            ADD CONSTRAINT FK_D72E133CAA23F6C8 FOREIGN KEY (next_id) 
            REFERENCES claro_subcontent (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_subcontent 
            ADD CONSTRAINT FK_D72E133CE9583FF0 FOREIGN KEY (back_id) 
            REFERENCES claro_subcontent (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_content2type 
            ADD CONSTRAINT FK_1A2084EF84A0A3ED FOREIGN KEY (content_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_content2type 
            ADD CONSTRAINT FK_1A2084EFC54C8C93 FOREIGN KEY (type_id) 
            REFERENCES claro_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_content2type 
            ADD CONSTRAINT FK_1A2084EFAA23F6C8 FOREIGN KEY (next_id) 
            REFERENCES claro_content2type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_content2type 
            ADD CONSTRAINT FK_1A2084EFE9583FF0 FOREIGN KEY (back_id) 
            REFERENCES claro_content2type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_content2region 
            ADD CONSTRAINT FK_8D18942E84A0A3ED FOREIGN KEY (content_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_content2region 
            ADD CONSTRAINT FK_8D18942E98260155 FOREIGN KEY (region_id) 
            REFERENCES claro_region (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_content2region 
            ADD CONSTRAINT FK_8D18942EAA23F6C8 FOREIGN KEY (next_id) 
            REFERENCES claro_content2region (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_content2region 
            ADD CONSTRAINT FK_8D18942EE9583FF0 FOREIGN KEY (back_id) 
            REFERENCES claro_content2region (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT FK_EBBE497282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT FK_EBBE4972A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT FK_EBBE497244BF891 FOREIGN KEY (widget_instance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget 
            ADD CONSTRAINT FK_76CA6C4FEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_simple_text_widget_config 
            ADD CONSTRAINT FK_C389EBCCAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65E896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65E89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_activity_rule_action 
            ADD CONSTRAINT FK_C8835D2098EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity_parameters 
            ADD CONSTRAINT FK_E2EE25E281C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A7DB5E3CF7 FOREIGN KEY (activityparameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A777C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            ADD CONSTRAINT FK_F461C538D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            ADD CONSTRAINT FK_F461C538A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            ADD CONSTRAINT FK_F461C53882D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag 
            ADD CONSTRAINT FK_C8EFD7EFA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag 
            ADD CONSTRAINT FK_C8EFD7EF82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_rel_workspace_tag 
            ADD CONSTRAINT FK_7883931082D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_rel_workspace_tag 
            ADD CONSTRAINT FK_78839310BAD26311 FOREIGN KEY (tag_id) 
            REFERENCES claro_workspace_tag (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag_hierarchy 
            ADD CONSTRAINT FK_A46B159EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag_hierarchy 
            ADD CONSTRAINT FK_A46B159EBAD26311 FOREIGN KEY (tag_id) 
            REFERENCES claro_workspace_tag (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag_hierarchy 
            ADD CONSTRAINT FK_A46B159E727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_workspace_tag (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_resource 
            ADD CONSTRAINT FK_F5D706351BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_resource 
            ADD CONSTRAINT FK_F5D706357975B7E7 FOREIGN KEY (model_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            ADD CONSTRAINT FK_323623448F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_text_revision 
            ADD CONSTRAINT FK_F61948DE698D3548 FOREIGN KEY (text_id) 
            REFERENCES claro_text (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_text_revision 
            ADD CONSTRAINT FK_F61948DEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_text 
            ADD CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC52410EEC FOREIGN KEY (primaryResource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC88BD9C1F FOREIGN KEY (parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            ADD CONSTRAINT FK_DA3985FFC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2E99038C0 FOREIGN KEY (panelFacet_id) 
            REFERENCES claro_panel_facet (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_user 
            DROP FOREIGN KEY FK_EB8D285282D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_role 
            DROP FOREIGN KEY FK_3177747182D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320E82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model 
            DROP FOREIGN KEY FK_536FFC4C82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCE82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23E82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            DROP FOREIGN KEY FK_F530F6BE82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A38582D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            DROP FOREIGN KEY FK_711A30B82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_widget_display_config 
            DROP FOREIGN KEY FK_EBBE497282D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            DROP FOREIGN KEY FK_F461C53882D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag 
            DROP FOREIGN KEY FK_C8EFD7EF82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_rel_workspace_tag 
            DROP FOREIGN KEY FK_7883931082D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D9028545A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_user_group 
            DROP FOREIGN KEY FK_ED8B34C7A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_user_role 
            DROP FOREIGN KEY FK_797E43FFA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_user 
            DROP FOREIGN KEY FK_5318388FA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF61220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            DROP FOREIGN KEY FK_35307C0AA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_user_options 
            DROP FOREIGN KEY FK_B2066972A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_api_access_token 
            DROP FOREIGN KEY FK_CE94828A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_api_auth_code 
            DROP FOREIGN KEY FK_9DFA457A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_api_refresh_token 
            DROP FOREIGN KEY FK_B1292B90A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F12D3860F
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FCD53EDB6
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F7E3C61F9
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCEA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            DROP FOREIGN KEY FK_F530F6BEA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_favourite 
            DROP FOREIGN KEY FK_711A30BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            DROP FOREIGN KEY FK_BC83196EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_widget_display_config 
            DROP FOREIGN KEY FK_EBBE4972A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_activity_evaluation 
            DROP FOREIGN KEY FK_F75EC869A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_activity_past_evaluation 
            DROP FOREIGN KEY FK_F1A76182A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            DROP FOREIGN KEY FK_F461C538A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag 
            DROP FOREIGN KEY FK_C8EFD7EFA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag_hierarchy 
            DROP FOREIGN KEY FK_A46B159EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_text_revision 
            DROP FOREIGN KEY FK_F61948DEA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_user_group 
            DROP FOREIGN KEY FK_ED8B34C7FE54D947
        ');
        $this->addSql('
            ALTER TABLE claro_group_role 
            DROP FOREIGN KEY FK_1CBA5A40FE54D947
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_group 
            DROP FOREIGN KEY FK_1F19A8AEFE54D947
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FC6F122B2
        ');
        $this->addSql('
            ALTER TABLE claro_user_role 
            DROP FOREIGN KEY FK_797E43FFD60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_group_role 
            DROP FOREIGN KEY FK_1CBA5A40D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_facet_role 
            DROP FOREIGN KEY FK_CDD5845DD60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_role 
            DROP FOREIGN KEY FK_12F52A52D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_general_facet_preference 
            DROP FOREIGN KEY FK_38AACF88D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tool_role 
            DROP FOREIGN KEY FK_94080069D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F483D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_tool_rights 
            DROP FOREIGN KEY FK_EFEDEC7ED60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_personnal_workspace_tool_config 
            DROP FOREIGN KEY FK_7A4A6A64D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_personal_workspace_resource_rights_management_access 
            DROP FOREIGN KEY FK_A3AE069AD60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_profile_property 
            DROP FOREIGN KEY FK_C2B93182D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FD60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_log_doer_platform_roles 
            DROP FOREIGN KEY FK_706568A5D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_log_doer_workspace_roles 
            DROP FOREIGN KEY FK_8A8D2F47D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP FOREIGN KEY FK_B81359F3D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            DROP FOREIGN KEY FK_F461C538D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F483B87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8158E0B66
        ');
        $this->addSql('
            ALTER TABLE claro_resource_shortcut 
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP FOREIGN KEY FK_12EEC186B87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_activity_rule 
            DROP FOREIGN KEY FK_6824A65E89329D25
        ');
        $this->addSql('
            ALTER TABLE claro_activity_secondary_resources 
            DROP FOREIGN KEY FK_713242A777C292AE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_resource 
            DROP FOREIGN KEY FK_F5D706351BAD783F
        ');
        $this->addSql('
            ALTER TABLE claro_file 
            DROP FOREIGN KEY FK_EA81C80BB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_text 
            DROP FOREIGN KEY FK_5D9559DCB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CAC52410EEC
        ');
        $this->addSql('
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CACB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_tool_rights 
            DROP FOREIGN KEY FK_EFEDEC7EBAC1B1D7
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_user 
            DROP FOREIGN KEY FK_5318388FD500BD91
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_group 
            DROP FOREIGN KEY FK_1F19A8AED500BD91
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_home_tab 
            DROP FOREIGN KEY FK_A8E0CB1BD500BD91
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_resource 
            DROP FOREIGN KEY FK_F5D706357975B7E7
        ');
        $this->addSql('
            ALTER TABLE claro_user 
            DROP FOREIGN KEY FK_EB8D28523ADB05F1
        ');
        $this->addSql('
            ALTER TABLE claro_api_access_token 
            DROP FOREIGN KEY FK_CE9482819EB6921
        ');
        $this->addSql('
            ALTER TABLE claro_api_auth_code 
            DROP FOREIGN KEY FK_9DFA45719EB6921
        ');
        $this->addSql('
            ALTER TABLE claro_api_refresh_token 
            DROP FOREIGN KEY FK_B1292B9019EB6921
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF98EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_resource_mask_decoder 
            DROP FOREIGN KEY FK_39D93F4298EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action 
            DROP FOREIGN KEY FK_1F57E52B98EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_list_type_creation 
            DROP FOREIGN KEY FK_84B4BEBA98EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F98EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_activity_rule_action 
            DROP FOREIGN KEY FK_C8835D2098EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_facet_role 
            DROP FOREIGN KEY FK_CDD5845DFC889F24
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            DROP FOREIGN KEY FK_DA3985FFC889F24
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tool_role 
            DROP FOREIGN KEY FK_940800692B80F4B6
        ');
        $this->addSql('
            ALTER TABLE claro_list_type_creation 
            DROP FOREIGN KEY FK_84B4BEBA195FBDF1
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF54B9D732
        ');
        $this->addSql('
            ALTER TABLE claro_resource_icon 
            DROP FOREIGN KEY FK_478C586179F0D498
        ');
        $this->addSql('
            ALTER TABLE claro_log_doer_platform_roles 
            DROP FOREIGN KEY FK_706568A5EA675D86
        ');
        $this->addSql('
            ALTER TABLE claro_log_doer_workspace_roles 
            DROP FOREIGN KEY FK_8A8D2F47EA675D86
        ');
        $this->addSql('
            ALTER TABLE claro_activity_evaluation 
            DROP FOREIGN KEY FK_F75EC869EA675D86
        ');
        $this->addSql('
            ALTER TABLE claro_activity_past_evaluation 
            DROP FOREIGN KEY FK_F1A76182EA675D86
        ');
        $this->addSql('
            ALTER TABLE claro_resource_type 
            DROP FOREIGN KEY FK_AEC62693EC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tools 
            DROP FOREIGN KEY FK_C10C14ECEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_tools 
            DROP FOREIGN KEY FK_60F90965EC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_theme 
            DROP FOREIGN KEY FK_1D76301AEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_widget 
            DROP FOREIGN KEY FK_76CA6C4FEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_model_home_tab 
            DROP FOREIGN KEY FK_A8E0CB1BCCE862F
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP FOREIGN KEY FK_B81359F3CCE862F
        ');
        $this->addSql('
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23E7D08FA9E
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            DROP FOREIGN KEY FK_F530F6BE7D08FA9E
        ');
        $this->addSql('
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23E44BF891
        ');
        $this->addSql('
            ALTER TABLE claro_log_widget_config 
            DROP FOREIGN KEY FK_C16334B2AB7B5A55
        ');
        $this->addSql('
            ALTER TABLE claro_widget_display_config 
            DROP FOREIGN KEY FK_EBBE497244BF891
        ');
        $this->addSql('
            ALTER TABLE claro_simple_text_widget_config 
            DROP FOREIGN KEY FK_C389EBCCAB7B5A55
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320E8F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_personnal_workspace_tool_config 
            DROP FOREIGN KEY FK_7A4A6A648F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            DROP FOREIGN KEY FK_323623448F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_subcontent 
            DROP FOREIGN KEY FK_D72E133C2055B9A2
        ');
        $this->addSql('
            ALTER TABLE claro_subcontent 
            DROP FOREIGN KEY FK_D72E133CDD62C21B
        ');
        $this->addSql('
            ALTER TABLE claro_content2type 
            DROP FOREIGN KEY FK_1A2084EF84A0A3ED
        ');
        $this->addSql('
            ALTER TABLE claro_content2region 
            DROP FOREIGN KEY FK_8D18942E84A0A3ED
        ');
        $this->addSql('
            ALTER TABLE claro_subcontent 
            DROP FOREIGN KEY FK_D72E133CAA23F6C8
        ');
        $this->addSql('
            ALTER TABLE claro_subcontent 
            DROP FOREIGN KEY FK_D72E133CE9583FF0
        ');
        $this->addSql('
            ALTER TABLE claro_content2type 
            DROP FOREIGN KEY FK_1A2084EFAA23F6C8
        ');
        $this->addSql('
            ALTER TABLE claro_content2type 
            DROP FOREIGN KEY FK_1A2084EFE9583FF0
        ');
        $this->addSql('
            ALTER TABLE claro_content2type 
            DROP FOREIGN KEY FK_1A2084EFC54C8C93
        ');
        $this->addSql('
            ALTER TABLE claro_content2region 
            DROP FOREIGN KEY FK_8D18942EAA23F6C8
        ');
        $this->addSql('
            ALTER TABLE claro_content2region 
            DROP FOREIGN KEY FK_8D18942EE9583FF0
        ');
        $this->addSql('
            ALTER TABLE claro_content2region 
            DROP FOREIGN KEY FK_8D18942E98260155
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385FBE885E2
        ');
        $this->addSql('
            ALTER TABLE claro_activity_rule 
            DROP FOREIGN KEY FK_6824A65E896F55DB
        ');
        $this->addSql('
            ALTER TABLE claro_activity_evaluation 
            DROP FOREIGN KEY FK_F75EC869896F55DB
        ');
        $this->addSql('
            ALTER TABLE claro_activity_past_evaluation 
            DROP FOREIGN KEY FK_F1A76182896F55DB
        ');
        $this->addSql('
            ALTER TABLE claro_activity_secondary_resources 
            DROP FOREIGN KEY FK_713242A7DB5E3CF7
        ');
        $this->addSql('
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CAC88BD9C1F
        ');
        $this->addSql('
            ALTER TABLE claro_rel_workspace_tag 
            DROP FOREIGN KEY FK_78839310BAD26311
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag_hierarchy 
            DROP FOREIGN KEY FK_A46B159EBAD26311
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_tag_hierarchy 
            DROP FOREIGN KEY FK_A46B159E727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_text_revision 
            DROP FOREIGN KEY FK_F61948DE698D3548
        ');
        $this->addSql('
            ALTER TABLE claro_activity_parameters 
            DROP FOREIGN KEY FK_E2EE25E281C06096
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP FOREIGN KEY FK_F6C21DB2E99038C0
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            DROP FOREIGN KEY FK_35307C0A9F9239AF
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_role 
            DROP FOREIGN KEY FK_12F52A529F9239AF
        ');
        $this->addSql('
            DROP TABLE claro_workspace
        ');
        $this->addSql('
            DROP TABLE claro_user
        ');
        $this->addSql('
            DROP TABLE claro_user_group
        ');
        $this->addSql('
            DROP TABLE claro_user_role
        ');
        $this->addSql('
            DROP TABLE claro_workspace_model_user
        ');
        $this->addSql('
            DROP TABLE claro_group
        ');
        $this->addSql('
            DROP TABLE claro_group_role
        ');
        $this->addSql('
            DROP TABLE claro_workspace_model_group
        ');
        $this->addSql('
            DROP TABLE claro_role
        ');
        $this->addSql('
            DROP TABLE claro_resource_node
        ');
        $this->addSql('
            DROP TABLE claro_ordered_tool
        ');
        $this->addSql('
            DROP TABLE claro_field_facet_value
        ');
        $this->addSql('
            DROP TABLE claro_workspace_model
        ');
        $this->addSql('
            DROP TABLE claro_workspace_model_home_tab
        ');
        $this->addSql('
            DROP TABLE claro_user_options
        ');
        $this->addSql('
            DROP TABLE claro_api_client
        ');
        $this->addSql('
            DROP TABLE claro_api_access_token
        ');
        $this->addSql('
            DROP TABLE claro_api_auth_code
        ');
        $this->addSql('
            DROP TABLE claro_api_refresh_token
        ');
        $this->addSql('
            DROP TABLE claro_resource_mask_decoder
        ');
        $this->addSql('
            DROP TABLE claro_resource_type
        ');
        $this->addSql('
            DROP TABLE claro_menu_action
        ');
        $this->addSql('
            DROP TABLE claro_facet
        ');
        $this->addSql('
            DROP TABLE claro_facet_role
        ');
        $this->addSql('
            DROP TABLE claro_field_facet_role
        ');
        $this->addSql('
            DROP TABLE claro_general_facet_preference
        ');
        $this->addSql('
            DROP TABLE claro_admin_tools
        ');
        $this->addSql('
            DROP TABLE claro_admin_tool_role
        ');
        $this->addSql('
            DROP TABLE claro_resource_rights
        ');
        $this->addSql('
            DROP TABLE claro_list_type_creation
        ');
        $this->addSql('
            DROP TABLE claro_tool_rights
        ');
        $this->addSql('
            DROP TABLE claro_personnal_workspace_tool_config
        ');
        $this->addSql('
            DROP TABLE claro_personal_workspace_resource_rights_management_access
        ');
        $this->addSql('
            DROP TABLE claro_profile_property
        ');
        $this->addSql('
            DROP TABLE claro_resource_icon
        ');
        $this->addSql('
            DROP TABLE claro_resource_shortcut
        ');
        $this->addSql('
            DROP TABLE claro_log
        ');
        $this->addSql('
            DROP TABLE claro_log_doer_platform_roles
        ');
        $this->addSql('
            DROP TABLE claro_log_doer_workspace_roles
        ');
        $this->addSql('
            DROP TABLE claro_plugin
        ');
        $this->addSql('
            DROP TABLE claro_directory
        ');
        $this->addSql('
            DROP TABLE claro_home_tab
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_roles
        ');
        $this->addSql('
            DROP TABLE claro_widget_home_tab_config
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_config
        ');
        $this->addSql('
            DROP TABLE claro_widget_instance
        ');
        $this->addSql('
            DROP TABLE claro_tools
        ');
        $this->addSql('
            DROP TABLE claro_workspace_favourite
        ');
        $this->addSql('
            DROP TABLE claro_content
        ');
        $this->addSql('
            DROP TABLE claro_content_translation
        ');
        $this->addSql('
            DROP TABLE claro_log_hidden_workspace_widget_config
        ');
        $this->addSql('
            DROP TABLE claro_log_widget_config
        ');
        $this->addSql('
            DROP TABLE claro_security_token
        ');
        $this->addSql('
            DROP TABLE claro_theme
        ');
        $this->addSql('
            DROP TABLE claro_subcontent
        ');
        $this->addSql('
            DROP TABLE claro_content2type
        ');
        $this->addSql('
            DROP TABLE claro_type
        ');
        $this->addSql('
            DROP TABLE claro_content2region
        ');
        $this->addSql('
            DROP TABLE claro_region
        ');
        $this->addSql('
            DROP TABLE claro_widget_display_config
        ');
        $this->addSql('
            DROP TABLE claro_widget
        ');
        $this->addSql('
            DROP TABLE claro_simple_text_widget_config
        ');
        $this->addSql('
            DROP TABLE claro_activity_rule
        ');
        $this->addSql('
            DROP TABLE claro_activity_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_activity_past_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_activity_rule_action
        ');
        $this->addSql('
            DROP TABLE claro_activity_parameters
        ');
        $this->addSql('
            DROP TABLE claro_activity_secondary_resources
        ');
        $this->addSql('
            DROP TABLE claro_session
        ');
        $this->addSql('
            DROP TABLE claro_workspace_registration_queue
        ');
        $this->addSql('
            DROP TABLE claro_workspace_tag
        ');
        $this->addSql('
            DROP TABLE claro_rel_workspace_tag
        ');
        $this->addSql('
            DROP TABLE claro_workspace_tag_hierarchy
        ');
        $this->addSql('
            DROP TABLE claro_bundle
        ');
        $this->addSql('
            DROP TABLE claro_workspace_model_resource
        ');
        $this->addSql('
            DROP TABLE claro_tool_mask_decoder
        ');
        $this->addSql('
            DROP TABLE claro_file
        ');
        $this->addSql('
            DROP TABLE claro_text_revision
        ');
        $this->addSql('
            DROP TABLE claro_text
        ');
        $this->addSql('
            DROP TABLE claro_activity
        ');
        $this->addSql('
            DROP TABLE claro_panel_facet
        ');
        $this->addSql('
            DROP TABLE claro_field_facet
        ');
    }
}
