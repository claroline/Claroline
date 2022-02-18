<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 07:42:00
 */
class Version20200610073540 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_panel_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                facet_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                position INT NOT NULL, 
                isDefaultCollapsed TINYINT(1) NOT NULL, 
                isEditable TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DA3985FD17F50A6 (uuid), 
                INDEX IDX_DA3985FFC889F24 (facet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                position INT NOT NULL, 
                forceCreationForm TINYINT(1) NOT NULL, 
                isMain TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 (name), 
                UNIQUE INDEX UNIQ_DCBA6D3AD17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_facet_role (
                facet_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_CDD5845DFC889F24 (facet_id), 
                INDEX IDX_CDD5845DD60322AC (role_id), 
                PRIMARY KEY(facet_id, role_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_node INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                type INT NOT NULL, 
                position INT DEFAULT NULL, 
                isRequired TINYINT(1) NOT NULL, 
                options LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                is_metadata TINYINT(1) DEFAULT '0' NOT NULL, 
                locked TINYINT(1) DEFAULT '0' NOT NULL, 
                locked_edition TINYINT(1) DEFAULT '0' NOT NULL, 
                help VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                hidden TINYINT(1) DEFAULT '0' NOT NULL, 
                panelFacet_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_F6C21DB2D17F50A6 (uuid), 
                INDEX IDX_F6C21DB2E99038C0 (panelFacet_id), 
                INDEX IDX_F6C21DB28A5F48FF (resource_node), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_panel_facet_role (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                canOpen TINYINT(1) NOT NULL, 
                canEdit TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                panelFacet_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_A66BF654D17F50A6 (uuid), 
                INDEX IDX_A66BF654D60322AC (role_id), 
                INDEX IDX_A66BF654E99038C0 (panelFacet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_field_facet_choice (
                id INT AUTO_INCREMENT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                position INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                fieldFacet_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_E2001DD17F50A6 (uuid), 
                INDEX IDX_E2001D9F9239AF (fieldFacet_id), 
                INDEX IDX_E2001D727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                license VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                showIcon TINYINT(1) DEFAULT '1' NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INT DEFAULT NULL, 
                path VARCHAR(3000) DEFAULT NULL, 
                materializedPath VARCHAR(3000) DEFAULT NULL, 
                value INT DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                published TINYINT(1) DEFAULT '1' NOT NULL, 
                author VARCHAR(255) DEFAULT NULL, 
                active TINYINT(1) DEFAULT '1' NOT NULL, 
                fullscreen TINYINT(1) NOT NULL, 
                accesses LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                views_count INT DEFAULT 0 NOT NULL, 
                deletable TINYINT(1) DEFAULT '1' NOT NULL, 
                comments_activated TINYINT(1) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                hidden TINYINT(1) DEFAULT '0' NOT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                UNIQUE INDEX UNIQ_A76799FF989D9B62 (slug), 
                UNIQUE INDEX UNIQ_A76799FFD17F50A6 (uuid), 
                INDEX IDX_A76799FF98EC6B7B (resource_type_id), 
                INDEX IDX_A76799FF727ACA70 (parent_id), 
                INDEX IDX_A76799FF82D40A1F (workspace_id), 
                INDEX IDX_A76799FF61220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet_value (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                stringValue LONGTEXT DEFAULT NULL, 
                floatValue DOUBLE PRECISION DEFAULT NULL, 
                dateValue DATETIME DEFAULT NULL, 
                arrayValue LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                boolValue TINYINT(1) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                fieldFacet_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_35307C0AD17F50A6 (uuid), 
                INDEX IDX_35307C0AA76ED395 (user_id), 
                INDEX IDX_35307C0A9F9239AF (fieldFacet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
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
                last_login DATETIME DEFAULT NULL, 
                initialization_date DATETIME DEFAULT NULL, 
                reset_password VARCHAR(255) DEFAULT NULL, 
                hash_time INT DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                hasAcceptedTerms TINYINT(1) DEFAULT NULL, 
                is_enabled TINYINT(1) NOT NULL, 
                is_removed TINYINT(1) NOT NULL, 
                is_locked TINYINT(1) NOT NULL, 
                is_mail_notified TINYINT(1) NOT NULL, 
                is_mail_validated TINYINT(1) NOT NULL, 
                public_url VARCHAR(255) DEFAULT NULL, 
                has_tuned_public_url TINYINT(1) NOT NULL, 
                expiration_date DATETIME DEFAULT NULL, 
                authentication VARCHAR(255) DEFAULT NULL, 
                email_validation_hash VARCHAR(255) DEFAULT NULL, 
                code VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_EB8D2852F85E0677 (username), 
                UNIQUE INDEX UNIQ_EB8D28525126AC48 (mail), 
                UNIQUE INDEX UNIQ_EB8D2852181F3A64 (public_url), 
                UNIQUE INDEX UNIQ_EB8D2852D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_EB8D285282D40A1F (workspace_id), 
                UNIQUE INDEX UNIQ_EB8D28523ADB05F1 (options_id), 
                INDEX code_idx (administrative_code), 
                INDEX enabled_idx (is_enabled), 
                INDEX is_removed (is_removed), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_group (
                user_id INT NOT NULL, 
                group_id INT NOT NULL, 
                INDEX IDX_ED8B34C7A76ED395 (user_id), 
                INDEX IDX_ED8B34C7FE54D947 (group_id), 
                PRIMARY KEY(user_id, group_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_role (
                user_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_797E43FFA76ED395 (user_id), 
                INDEX IDX_797E43FFD60322AC (role_id), 
                PRIMARY KEY(user_id, role_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_administrator (
                user_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_22EB9B3AA76ED395 (user_id), 
                INDEX IDX_22EB9B3A32C8A3DE (organization_id), 
                PRIMARY KEY(user_id, organization_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE user_location (
                user_id INT NOT NULL, 
                location_id INT NOT NULL, 
                INDEX IDX_BE136DCBA76ED395 (user_id), 
                INDEX IDX_BE136DCB64D218E (location_id), 
                PRIMARY KEY(user_id, location_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_317774715E237E06 (name), 
                UNIQUE INDEX UNIQ_31777471D17F50A6 (uuid), 
                INDEX IDX_3177747182D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__organization (
                id INT AUTO_INCREMENT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                position INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                email VARCHAR(255) DEFAULT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT DEFAULT NULL, 
                is_default TINYINT(1) NOT NULL, 
                vat VARCHAR(255) DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                maxUsers INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                code VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_B68DD0D5D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_B68DD0D577153098 (code), 
                INDEX IDX_B68DD0D5727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__location_organization (
                organization_id INT NOT NULL, 
                location_id INT NOT NULL, 
                INDEX IDX_C4EBDE032C8A3DE (organization_id), 
                INDEX IDX_C4EBDE064D218E (location_id), 
                PRIMARY KEY(organization_id, location_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__location (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                type INT NOT NULL, 
                street VARCHAR(255) NOT NULL, 
                streetNumber VARCHAR(255) NOT NULL, 
                boxNumber VARCHAR(255) DEFAULT NULL, 
                pc VARCHAR(255) NOT NULL, 
                town VARCHAR(255) NOT NULL, 
                country VARCHAR(255) NOT NULL, 
                latitude DOUBLE PRECISION DEFAULT NULL, 
                longitude DOUBLE PRECISION DEFAULT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_24C849F7D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_workspace (
                id INT AUTO_INCREMENT NOT NULL, 
                default_role_id INT DEFAULT NULL, 
                options_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                maxStorageSize VARCHAR(255) NOT NULL, 
                lang VARCHAR(255) DEFAULT NULL, 
                maxUploadResources INT NOT NULL, 
                maxUsers INT NOT NULL, 
                displayable TINYINT(1) NOT NULL, 
                isModel TINYINT(1) NOT NULL, 
                self_registration TINYINT(1) NOT NULL, 
                registration_validation TINYINT(1) NOT NULL, 
                self_unregistration TINYINT(1) NOT NULL, 
                creation_date INT DEFAULT NULL, 
                is_personal TINYINT(1) NOT NULL, 
                workspace_type INT DEFAULT NULL, 
                disabled_notifications TINYINT(1) NOT NULL, 
                showProgression TINYINT(1) NOT NULL, 
                archived TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                access_code VARCHAR(255) DEFAULT NULL, 
                allowed_ips LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                UNIQUE INDEX UNIQ_D902854577153098 (code), 
                UNIQUE INDEX UNIQ_D9028545989D9B62 (slug), 
                UNIQUE INDEX UNIQ_D9028545D17F50A6 (uuid), 
                INDEX IDX_D9028545248673E9 (default_role_id), 
                UNIQUE INDEX UNIQ_D90285453ADB05F1 (options_id), 
                INDEX IDX_D902854561220EA6 (creator_id), 
                INDEX name_idx (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE workspace_organization (
                workspace_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_D212AD8082D40A1F (workspace_id), 
                INDEX IDX_D212AD8032C8A3DE (organization_id), 
                PRIMARY KEY(workspace_id, organization_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_group (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_read_only TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_E7C393D7D17F50A6 (uuid), 
                UNIQUE INDEX group_unique_name (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_group_role (
                group_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_1CBA5A40FE54D947 (group_id), 
                INDEX IDX_1CBA5A40D60322AC (role_id), 
                PRIMARY KEY(group_id, role_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE group_organization (
                group_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_2DA82945FE54D947 (group_id), 
                INDEX IDX_2DA8294532C8A3DE (organization_id), 
                PRIMARY KEY(group_id, organization_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE group_location (
                group_id INT NOT NULL, 
                location_id INT NOT NULL, 
                INDEX IDX_57AEC5B4FE54D947 (group_id), 
                INDEX IDX_57AEC5B464D218E (location_id), 
                PRIMARY KEY(group_id, location_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE user_organization (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                oganization_id INT NOT NULL, 
                is_main TINYINT(1) NOT NULL, 
                INDEX IDX_41221F7EA76ED395 (user_id), 
                INDEX IDX_41221F7EF35E13B7 (oganization_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cryptographic_key (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                organization_id INT DEFAULT NULL, 
                publicKeyParam LONGTEXT NOT NULL, 
                privateKeyParam LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_1603A182D17F50A6 (uuid), 
                INDEX IDX_1603A182A76ED395 (user_id), 
                INDEX IDX_1603A18232C8A3DE (organization_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_admin_tools (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_C10C14ECD17F50A6 (uuid), 
                INDEX IDX_C10C14ECEC942BCF (plugin_id), 
                UNIQUE INDEX admin_tool_plugin_unique (name, plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_admin_tool_role (
                admintool_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_940800692B80F4B6 (admintool_id), 
                INDEX IDX_94080069D60322AC (role_id), 
                PRIMARY KEY(admintool_id, role_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_resource_rights (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                mask INT NOT NULL, 
                resourceNode_id INT NOT NULL, 
                INDEX IDX_3848F483D60322AC (role_id), 
                INDEX IDX_3848F483B87FAB32 (resourceNode_id), 
                INDEX mask_idx (mask), 
                UNIQUE INDEX resource_rights_unique_resource_role (resourceNode_id, role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_workspace_shortcuts (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                role_id INT DEFAULT NULL, 
                shortcuts_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_872A8149D17F50A6 (uuid), 
                INDEX IDX_872A814982D40A1F (workspace_id), 
                INDEX IDX_872A8149D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                tool_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                showIcon TINYINT(1) DEFAULT '0' NOT NULL, 
                display_order INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_6CF1320ED17F50A6 (uuid), 
                INDEX IDX_6CF1320E82D40A1F (workspace_id), 
                INDEX IDX_6CF1320E8F7B22CC (tool_id), 
                INDEX IDX_6CF1320EA76ED395 (user_id), 
                UNIQUE INDEX ordered_tool_unique_tool_user_type (tool_id, user_id), 
                UNIQUE INDEX ordered_tool_unique_tool_ws_type (tool_id, workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_options (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                breadcrumbItems LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                UNIQUE INDEX UNIQ_D603AE0582D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_template_type (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                type_name VARCHAR(255) NOT NULL, 
                placeholders LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                default_template VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_7428AC44D17F50A6 (uuid), 
                INDEX IDX_7428AC44EC942BCF (plugin_id), 
                UNIQUE INDEX template_unique_type (type_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_plugin (
                id INT AUTO_INCREMENT NOT NULL, 
                vendor_name VARCHAR(50) NOT NULL, 
                short_name VARCHAR(50) NOT NULL, 
                has_options TINYINT(1) NOT NULL, 
                UNIQUE INDEX plugin_unique_name (vendor_name, short_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_template (
                id INT AUTO_INCREMENT NOT NULL, 
                claro_template_type INT NOT NULL, 
                template_name VARCHAR(255) NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                lang VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DFB26A75D17F50A6 (uuid), 
                INDEX IDX_DFB26A757428AC44 (claro_template_type), 
                UNIQUE INDEX template_unique_name (template_name, lang), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_user_options (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                desktop_background_color VARCHAR(255) DEFAULT NULL, 
                desktop_mode INT DEFAULT 1 NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                UNIQUE INDEX UNIQ_B2066972A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_resource_mask_decoder (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT NOT NULL, 
                value INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_39D93F4298EC6B7B (resource_type_id), 
                INDEX value (value), 
                INDEX name (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_resource_type (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(256) NOT NULL, 
                is_exportable TINYINT(1) NOT NULL, 
                tags LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                defaultMask INT NOT NULL, 
                is_enabled TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_AEC626935E237E06 (name), 
                INDEX IDX_AEC62693EC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_menu_action (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT DEFAULT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                decoder VARCHAR(255) NOT NULL, 
                group_name VARCHAR(255) DEFAULT NULL, 
                scope LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                api LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                is_default TINYINT(1) NOT NULL, 
                INDEX IDX_1F57E52B98EC6B7B (resource_type_id), 
                INDEX IDX_1F57E52BEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_log (
                id INT AUTO_INCREMENT NOT NULL, 
                doer_id INT DEFAULT NULL, 
                receiver_id INT DEFAULT NULL, 
                receiver_group_id INT DEFAULT NULL, 
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
                other_element_id INT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                INDEX IDX_97FAB91F12D3860F (doer_id), 
                INDEX IDX_97FAB91FCD53EDB6 (receiver_id), 
                INDEX IDX_97FAB91FC6F122B2 (receiver_group_id), 
                INDEX IDX_97FAB91F82D40A1F (workspace_id), 
                INDEX IDX_97FAB91FB87FAB32 (resourceNode_id), 
                INDEX IDX_97FAB91F98EC6B7B (resource_type_id), 
                INDEX IDX_97FAB91FD60322AC (role_id), 
                INDEX action_idx (action), 
                INDEX tool_idx (tool_name), 
                INDEX doer_type_idx (doer_type), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_resource_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                resource_node_id INT NOT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_79F5F969D17F50A6 (uuid), 
                INDEX IDX_79F5F969A76ED395 (user_id), 
                INDEX IDX_79F5F9691BAD783F (resource_node_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_tool_mask_decoder (
                id INT AUTO_INCREMENT NOT NULL, 
                tool_id INT NOT NULL, 
                value INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_323623448F7B22CC (tool_id), 
                UNIQUE INDEX tool_mask_decoder_unique_tool_and_name (tool_id, name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_tools (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                is_workspace_required TINYINT(1) NOT NULL, 
                is_desktop_required TINYINT(1) NOT NULL, 
                is_displayable_in_workspace TINYINT(1) NOT NULL, 
                is_displayable_in_desktop TINYINT(1) NOT NULL, 
                is_exportable TINYINT(1) NOT NULL, 
                is_configurable_in_workspace TINYINT(1) NOT NULL, 
                is_configurable_in_desktop TINYINT(1) NOT NULL, 
                is_locked_for_admin TINYINT(1) NOT NULL, 
                is_anonymous_excluded TINYINT(1) NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_60F90965D17F50A6 (uuid), 
                INDEX IDX_60F90965EC942BCF (plugin_id), 
                UNIQUE INDEX tool_plugin_unique (name, plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_connection_message_slide (
                id INT AUTO_INCREMENT NOT NULL, 
                message_id INT DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                slide_order INT NOT NULL, 
                shortcuts LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                uuid VARCHAR(36) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_DBB5C281D17F50A6 (uuid), 
                INDEX IDX_DBB5C281537A1329 (message_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_connection_message (
                id INT AUTO_INCREMENT NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                message_type VARCHAR(255) NOT NULL, 
                locked TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                hidden TINYINT(1) DEFAULT '0' NOT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                UNIQUE INDEX UNIQ_590DE667D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_connection_message_role (
                connectionmessage_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_B1EB3A86E926F912 (connectionmessage_id), 
                INDEX IDX_B1EB3A86D60322AC (role_id), 
                PRIMARY KEY(connectionmessage_id, role_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_connection_message_user (
                connectionmessage_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_6B1166A5E926F912 (connectionmessage_id), 
                INDEX IDX_6B1166A5A76ED395 (user_id), 
                PRIMARY KEY(connectionmessage_id, user_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_public_file (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                file_size INT DEFAULT NULL, 
                filename VARCHAR(255) NOT NULL, 
                hash_name VARCHAR(255) NOT NULL, 
                directory_name VARCHAR(255) NOT NULL, 
                creation_date DATETIME NOT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                source_type VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_7C1E45A0A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_directory (
                id INT AUTO_INCREMENT NOT NULL, 
                is_upload_destination TINYINT(1) NOT NULL, 
                filterable TINYINT(1) NOT NULL, 
                sortable TINYINT(1) NOT NULL, 
                paginated TINYINT(1) NOT NULL, 
                columnsFilterable TINYINT(1) NOT NULL, 
                count TINYINT(1) NOT NULL, 
                actions TINYINT(1) NOT NULL, 
                sortBy VARCHAR(255) DEFAULT NULL, 
                availableSort LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                pageSize INT NOT NULL, 
                availablePageSizes LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                display VARCHAR(255) NOT NULL, 
                availableDisplays LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                searchMode VARCHAR(255) DEFAULT NULL, 
                filters LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                availableFilters LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                availableColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                displayedColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                card LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_12EEC186B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_file (
                id INT AUTO_INCREMENT NOT NULL, 
                size INT NOT NULL, 
                hash_name VARCHAR(255) NOT NULL, 
                autoDownload TINYINT(1) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_EA81C80BE1F029B6 (hash_name), 
                UNIQUE INDEX UNIQ_EA81C80BB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_resource_user_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_node INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                nb_attempts INT NOT NULL, 
                nb_openings INT NOT NULL, 
                required TINYINT(1) NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                score_min DOUBLE PRECISION DEFAULT NULL, 
                score_max DOUBLE PRECISION DEFAULT NULL, 
                progression INT DEFAULT NULL, 
                progression_max INT DEFAULT NULL, 
                INDEX IDX_BCA02E7A8A5F48FF (resource_node), 
                INDEX IDX_BCA02E7AA76ED395 (user_id), 
                UNIQUE INDEX resource_user_evaluation (resource_node, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_resource_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_user_evaluation INT DEFAULT NULL, 
                evaluation_comment LONGTEXT DEFAULT NULL, 
                more_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                score_min DOUBLE PRECISION DEFAULT NULL, 
                score_max DOUBLE PRECISION DEFAULT NULL, 
                progression INT DEFAULT NULL, 
                progression_max INT DEFAULT NULL, 
                INDEX IDX_C2A4B1E7FBE9DF40 (resource_user_evaluation), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_log_connect_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                resource_name VARCHAR(255) NOT NULL, 
                resource_type VARCHAR(255) NOT NULL, 
                embedded TINYINT(1) NOT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_CBEC498D17F50A6 (uuid), 
                INDEX IDX_CBEC49889329D25 (resource_id), 
                INDEX IDX_CBEC498A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                activity_parameters_id INT NOT NULL, 
                log_id INT DEFAULT NULL, 
                lastest_evaluation_date DATETIME DEFAULT NULL, 
                total_duration INT DEFAULT NULL, 
                attempts_count INT DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                INDEX IDX_F75EC869A76ED395 (user_id), 
                INDEX IDX_F75EC869896F55DB (activity_parameters_id), 
                INDEX IDX_F75EC869EA675D86 (log_id), 
                UNIQUE INDEX user_activity_unique_evaluation (user_id, activity_parameters_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_api_token (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                token VARCHAR(36) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_2F3470B75F37A13B (token), 
                UNIQUE INDEX UNIQ_2F3470B7D17F50A6 (uuid), 
                INDEX IDX_2F3470B7A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_database_backup (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                table_name VARCHAR(255) DEFAULT NULL, 
                reason VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME DEFAULT NULL, 
                batch VARCHAR(255) DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_data_source (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                source_name VARCHAR(255) NOT NULL, 
                source_type VARCHAR(255) NOT NULL, 
                context LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                tags LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_B4A87F0BD17F50A6 (uuid), 
                INDEX IDX_B4A87F0BEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_general_facet_preference (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                baseData TINYINT(1) NOT NULL, 
                email TINYINT(1) NOT NULL, 
                phone TINYINT(1) NOT NULL, 
                sendMail TINYINT(1) NOT NULL, 
                sendMessage TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_38AACF88D17F50A6 (uuid), 
                INDEX IDX_38AACF88D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_public_file_use (
                id INT AUTO_INCREMENT NOT NULL, 
                public_file_id INT DEFAULT NULL, 
                object_uuid VARCHAR(255) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                object_name VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_6F128157C81526DE (public_file_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');

        $this->addSql('
            CREATE TABLE claro_log_connect_admin_tool (
                id INT AUTO_INCREMENT NOT NULL, 
                tool_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                tool_name VARCHAR(255) NOT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_83338977D17F50A6 (uuid), 
                INDEX IDX_833389778F7B22CC (tool_id), 
                INDEX IDX_83338977A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_log_connect_platform (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_897DE045D17F50A6 (uuid), 
                INDEX IDX_897DE045A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_log_connect_tool (
                id INT AUTO_INCREMENT NOT NULL, 
                tool_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                tool_name VARCHAR(255) NOT NULL, 
                original_tool_name VARCHAR(255) NOT NULL, 
                workspace_name VARCHAR(255) DEFAULT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DDD8A470D17F50A6 (uuid), 
                INDEX IDX_DDD8A4708F7B22CC (tool_id), 
                INDEX IDX_DDD8A47082D40A1F (workspace_id), 
                INDEX IDX_DDD8A470A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_log_connect_workspace (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_name VARCHAR(255) NOT NULL, 
                connection_date DATETIME NOT NULL, 
                total_duration INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_8724810ED17F50A6 (uuid), 
                INDEX IDX_8724810E82D40A1F (workspace_id), 
                INDEX IDX_8724810EA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_object_lock (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                object_uuid VARCHAR(255) NOT NULL, 
                object_class VARCHAR(255) NOT NULL, 
                locked TINYINT(1) NOT NULL, 
                creation_date DATETIME NOT NULL, 
                INDEX IDX_9146967CA76ED395 (user_id), 
                UNIQUE INDEX `unique` (object_uuid, object_class), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_text (
                id INT AUTO_INCREMENT NOT NULL, 
                version INT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5D9559DCB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_role_options (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                UNIQUE INDEX UNIQ_56C6D283D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_security_token (
                id INT AUTO_INCREMENT NOT NULL, 
                client_name VARCHAR(255) NOT NULL, 
                token VARCHAR(255) NOT NULL, 
                client_ip VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_B3A67A408FBFBD64 (client_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_session (
                session_id VARCHAR(255) NOT NULL, 
                session_data LONGTEXT NOT NULL, 
                session_time INT NOT NULL, 
                PRIMARY KEY(session_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_home_tab (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_A9744CCED17F50A6 (uuid), 
                INDEX IDX_A9744CCEA76ED395 (user_id), 
                INDEX IDX_A9744CCE82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_home_tab_config (
                id INT AUTO_INCREMENT NOT NULL, 
                home_tab_id INT NOT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                longTitle LONGTEXT NOT NULL, 
                centerTitle TINYINT(1) NOT NULL, 
                icon VARCHAR(255) DEFAULT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                is_visible TINYINT(1) NOT NULL, 
                tab_order INT NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                INDEX IDX_F530F6BE7D08FA9E (home_tab_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_home_tab_roles (
                hometabconfig_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_B81359F339727CCF (hometabconfig_id), 
                INDEX IDX_B81359F3D60322AC (role_id), 
                PRIMARY KEY(hometabconfig_id, role_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_version (
                id INT AUTO_INCREMENT NOT NULL, 
                commit VARCHAR(255) NOT NULL, 
                version VARCHAR(255) NOT NULL, 
                branch VARCHAR(255) NOT NULL, 
                bundle VARCHAR(255) NOT NULL, 
                is_upgraded TINYINT(1) NOT NULL, 
                date INT DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_widget_list (
                id INT AUTO_INCREMENT NOT NULL, 
                maxResults INT DEFAULT NULL, 
                filterable TINYINT(1) NOT NULL, 
                sortable TINYINT(1) NOT NULL, 
                paginated TINYINT(1) NOT NULL, 
                columnsFilterable TINYINT(1) NOT NULL, 
                count TINYINT(1) NOT NULL, 
                actions TINYINT(1) NOT NULL, 
                sortBy VARCHAR(255) DEFAULT NULL, 
                availableSort LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                pageSize INT NOT NULL, 
                availablePageSizes LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                display VARCHAR(255) NOT NULL, 
                availableDisplays LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                searchMode VARCHAR(255) DEFAULT NULL, 
                filters LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                availableFilters LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                availableColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                displayedColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                card LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_57E3C2C6AB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_widget_profile (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                currentUser TINYINT(1) NOT NULL, 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_8F55951FAB7B5A55 (widgetInstance_id), 
                INDEX IDX_8F55951FA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_widget_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                node_id INT DEFAULT NULL, 
                showResourceHeader TINYINT(1) NOT NULL, 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_A128E64DAB7B5A55 (widgetInstance_id), 
                INDEX IDX_A128E64D460D9FD7 (node_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_widget_simple (
                id INT AUTO_INCREMENT NOT NULL, 
                content LONGTEXT NOT NULL, 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_18CC1F0AAB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_widget (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) DEFAULT NULL, 
                sources LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                context LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                is_exportable TINYINT(1) NOT NULL, 
                tags LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_76CA6C4FD17F50A6 (uuid), 
                INDEX IDX_76CA6C4FEC942BCF (plugin_id), 
                UNIQUE INDEX widget_plugin_unique (name, plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_widget_container (
                id INT AUTO_INCREMENT NOT NULL, 
                hometab_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_3B06DD75D17F50A6 (uuid), 
                INDEX IDX_3B06DD75CCE862F (hometab_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_widget_container_config (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_container_id INT DEFAULT NULL, 
                widget_name VARCHAR(255) DEFAULT NULL, 
                alignName VARCHAR(255) NOT NULL, 
                is_visible TINYINT(1) NOT NULL, 
                layout LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                color VARCHAR(255) DEFAULT NULL, 
                borderColor VARCHAR(255) DEFAULT NULL, 
                backgroundType VARCHAR(255) NOT NULL, 
                background VARCHAR(255) DEFAULT NULL, 
                position INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_9523B282D17F50A6 (uuid), 
                INDEX IDX_9523B282581122C3 (widget_container_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_widget_instance (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_id INT NOT NULL, 
                container_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                dataSource_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5F89A385D17F50A6 (uuid), 
                INDEX IDX_5F89A385FBE885E2 (widget_id), 
                INDEX IDX_5F89A385BC21F742 (container_id), 
                INDEX IDX_5F89A385F3D3127E (dataSource_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_widget_instance_config (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_instance_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                widget_order INT NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                is_visible TINYINT(1) NOT NULL, 
                is_locked TINYINT(1) NOT NULL, 
                INDEX IDX_4787A3FD44BF891 (widget_instance_id), 
                INDEX IDX_4787A3FDA76ED395 (user_id), 
                INDEX IDX_4787A3FD82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_code VARCHAR(255) NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                score_min DOUBLE PRECISION DEFAULT NULL, 
                score_max DOUBLE PRECISION DEFAULT NULL, 
                progression INT DEFAULT NULL, 
                progression_max INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_E0FF6754D17F50A6 (uuid), 
                INDEX IDX_E0FF675482D40A1F (workspace_id), 
                INDEX IDX_E0FF6754A76ED395 (user_id), 
                UNIQUE INDEX workspace_user_evaluation (workspace_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_requirements (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                role_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_894BE409D17F50A6 (uuid), 
                INDEX IDX_894BE40982D40A1F (workspace_id), 
                INDEX IDX_894BE409A76ED395 (user_id), 
                INDEX IDX_894BE409D60322AC (role_id), 
                UNIQUE INDEX workspace_user_requirements (workspace_id, user_id), 
                UNIQUE INDEX workspace_role_requirements (workspace_id, role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_required_resources (
                requirements_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                INDEX IDX_85A0B2D9296B0ED5 (requirements_id), 
                INDEX IDX_85A0B2D977C292AE (resourcenode_id), 
                PRIMARY KEY(
                    requirements_id, resourcenode_id
                )
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            ADD CONSTRAINT FK_DA3985FFC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2E99038C0 FOREIGN KEY (panelFacet_id) 
            REFERENCES claro_panel_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB28A5F48FF FOREIGN KEY (resource_node) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet_role 
            ADD CONSTRAINT FK_A66BF654D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet_role 
            ADD CONSTRAINT FK_A66BF654E99038C0 FOREIGN KEY (panelFacet_id) 
            REFERENCES claro_panel_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            ADD CONSTRAINT FK_E2001D9F9239AF FOREIGN KEY (fieldFacet_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            ADD CONSTRAINT FK_E2001D727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_field_facet_choice (id) 
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
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
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
            ALTER TABLE claro_user_administrator 
            ADD CONSTRAINT FK_22EB9B3AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            ADD CONSTRAINT FK_22EB9B3A32C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE user_location 
            ADD CONSTRAINT FK_BE136DCBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE user_location 
            ADD CONSTRAINT FK_BE136DCB64D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_role 
            ADD CONSTRAINT FK_3177747182D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__organization 
            ADD CONSTRAINT FK_B68DD0D5727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__location_organization 
            ADD CONSTRAINT FK_C4EBDE032C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__location_organization 
            ADD CONSTRAINT FK_C4EBDE064D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D9028545248673E9 FOREIGN KEY (default_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D90285453ADB05F1 FOREIGN KEY (options_id) 
            REFERENCES claro_workspace_options (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D902854561220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE workspace_organization 
            ADD CONSTRAINT FK_D212AD8082D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE workspace_organization 
            ADD CONSTRAINT FK_D212AD8032C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
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
            ALTER TABLE group_organization 
            ADD CONSTRAINT FK_2DA82945FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE group_organization 
            ADD CONSTRAINT FK_2DA8294532C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE group_location 
            ADD CONSTRAINT FK_57AEC5B4FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE group_location 
            ADD CONSTRAINT FK_57AEC5B464D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE user_organization 
            ADD CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE user_organization 
            ADD CONSTRAINT FK_41221F7EF35E13B7 FOREIGN KEY (oganization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            ADD CONSTRAINT FK_1603A182A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            ADD CONSTRAINT FK_1603A18232C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id)
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
            ALTER TABLE claro_workspace_shortcuts 
            ADD CONSTRAINT FK_872A814982D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_shortcuts 
            ADD CONSTRAINT FK_872A8149D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
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
            ALTER TABLE claro_workspace_options 
            ADD CONSTRAINT FK_D603AE0582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_template_type 
            ADD CONSTRAINT FK_7428AC44EC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_template 
            ADD CONSTRAINT FK_DFB26A757428AC44 FOREIGN KEY (claro_template_type) 
            REFERENCES claro_template_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_options 
            ADD CONSTRAINT FK_B2066972A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
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
            ALTER TABLE claro_menu_action 
            ADD CONSTRAINT FK_1F57E52BEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
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
            ALTER TABLE claro_resource_comment 
            ADD CONSTRAINT FK_79F5F969A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_comment 
            ADD CONSTRAINT FK_79F5F9691BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            ADD CONSTRAINT FK_323623448F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_tools 
            ADD CONSTRAINT FK_60F90965EC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');

        $this->addSql('
            ALTER TABLE claro_connection_message_slide 
            ADD CONSTRAINT FK_DBB5C281537A1329 FOREIGN KEY (message_id) 
            REFERENCES claro_connection_message (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_role 
            ADD CONSTRAINT FK_B1EB3A86E926F912 FOREIGN KEY (connectionmessage_id) 
            REFERENCES claro_connection_message (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_role 
            ADD CONSTRAINT FK_B1EB3A86D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_user 
            ADD CONSTRAINT FK_6B1166A5E926F912 FOREIGN KEY (connectionmessage_id) 
            REFERENCES claro_connection_message (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_user 
            ADD CONSTRAINT FK_6B1166A5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_public_file 
            ADD CONSTRAINT FK_7C1E45A0A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_file 
            ADD CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD CONSTRAINT FK_BCA02E7A8A5F48FF FOREIGN KEY (resource_node) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD CONSTRAINT FK_BCA02E7AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            ADD CONSTRAINT FK_C2A4B1E7FBE9DF40 FOREIGN KEY (resource_user_evaluation) 
            REFERENCES claro_resource_user_evaluation (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_resource 
            ADD CONSTRAINT FK_CBEC49889329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_resource 
            ADD CONSTRAINT FK_CBEC498A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
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
            ALTER TABLE claro_api_token 
            ADD CONSTRAINT FK_2F3470B7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_data_source 
            ADD CONSTRAINT FK_B4A87F0BEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_general_facet_preference 
            ADD CONSTRAINT FK_38AACF88D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_public_file_use 
            ADD CONSTRAINT FK_6F128157C81526DE FOREIGN KEY (public_file_id) 
            REFERENCES claro_public_file (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_admin_tool 
            ADD CONSTRAINT FK_833389778F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_admin_tools (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_admin_tool 
            ADD CONSTRAINT FK_83338977A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_platform 
            ADD CONSTRAINT FK_897DE045A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            ADD CONSTRAINT FK_DDD8A4708F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_ordered_tool (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            ADD CONSTRAINT FK_DDD8A47082D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            ADD CONSTRAINT FK_DDD8A470A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_workspace 
            ADD CONSTRAINT FK_8724810E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_workspace 
            ADD CONSTRAINT FK_8724810EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_object_lock 
            ADD CONSTRAINT FK_9146967CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
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
            ALTER TABLE claro_role_options 
            ADD CONSTRAINT FK_56C6D283D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
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
            ALTER TABLE claro_home_tab_config 
            ADD CONSTRAINT FK_F530F6BE7D08FA9E FOREIGN KEY (home_tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F339727CCF FOREIGN KEY (hometabconfig_id) 
            REFERENCES claro_home_tab_config (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F3D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            ADD CONSTRAINT FK_57E3C2C6AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_profile 
            ADD CONSTRAINT FK_8F55951FAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_profile 
            ADD CONSTRAINT FK_8F55951FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_widget_resource 
            ADD CONSTRAINT FK_A128E64DAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_resource 
            ADD CONSTRAINT FK_A128E64D460D9FD7 FOREIGN KEY (node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_widget_simple 
            ADD CONSTRAINT FK_18CC1F0AAB7B5A55 FOREIGN KEY (widgetInstance_id) 
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
            ALTER TABLE claro_widget_container 
            ADD CONSTRAINT FK_3B06DD75CCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container_config 
            ADD CONSTRAINT FK_9523B282581122C3 FOREIGN KEY (widget_container_id) 
            REFERENCES claro_widget_container (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES claro_widget (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385BC21F742 FOREIGN KEY (container_id) 
            REFERENCES claro_widget_container (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385F3D3127E FOREIGN KEY (dataSource_id) 
            REFERENCES claro_data_source (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance_config 
            ADD CONSTRAINT FK_4787A3FD44BF891 FOREIGN KEY (widget_instance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance_config 
            ADD CONSTRAINT FK_4787A3FDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance_config 
            ADD CONSTRAINT FK_4787A3FD82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD CONSTRAINT FK_E0FF675482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD CONSTRAINT FK_E0FF6754A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_requirements 
            ADD CONSTRAINT FK_894BE40982D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_requirements 
            ADD CONSTRAINT FK_894BE409A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_requirements 
            ADD CONSTRAINT FK_894BE409D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_required_resources 
            ADD CONSTRAINT FK_85A0B2D9296B0ED5 FOREIGN KEY (requirements_id) 
            REFERENCES claro_workspace_requirements (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_required_resources 
            ADD CONSTRAINT FK_85A0B2D977C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP FOREIGN KEY FK_F6C21DB2E99038C0
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet_role 
            DROP FOREIGN KEY FK_A66BF654E99038C0
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            DROP FOREIGN KEY FK_DA3985FFC889F24
        ');
        $this->addSql('
            ALTER TABLE claro_facet_role 
            DROP FOREIGN KEY FK_CDD5845DFC889F24
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            DROP FOREIGN KEY FK_E2001D9F9239AF
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            DROP FOREIGN KEY FK_35307C0A9F9239AF
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            DROP FOREIGN KEY FK_E2001D727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP FOREIGN KEY FK_F6C21DB28A5F48FF
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
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_resource_comment 
            DROP FOREIGN KEY FK_79F5F9691BAD783F
        ');
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP FOREIGN KEY FK_12EEC186B87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_file 
            DROP FOREIGN KEY FK_EA81C80BB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP FOREIGN KEY FK_BCA02E7A8A5F48FF
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_resource 
            DROP FOREIGN KEY FK_CBEC49889329D25
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
            ALTER TABLE claro_activity_secondary_resources 
            DROP FOREIGN KEY FK_713242A777C292AE
        ');
        $this->addSql('
            ALTER TABLE claro_text 
            DROP FOREIGN KEY FK_5D9559DCB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_widget_resource 
            DROP FOREIGN KEY FK_A128E64D460D9FD7
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_required_resources 
            DROP FOREIGN KEY FK_85A0B2D977C292AE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF61220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            DROP FOREIGN KEY FK_35307C0AA76ED395
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
            ALTER TABLE claro_user_administrator 
            DROP FOREIGN KEY FK_22EB9B3AA76ED395
        ');
        $this->addSql('
            ALTER TABLE user_location 
            DROP FOREIGN KEY FK_BE136DCBA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D902854561220EA6
        ');
        $this->addSql('
            ALTER TABLE user_organization 
            DROP FOREIGN KEY FK_41221F7EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            DROP FOREIGN KEY FK_1603A182A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_user_options 
            DROP FOREIGN KEY FK_B2066972A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            DROP FOREIGN KEY FK_F461C538A76ED395
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
            ALTER TABLE claro_resource_comment 
            DROP FOREIGN KEY FK_79F5F969A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_user 
            DROP FOREIGN KEY FK_6B1166A5A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_public_file 
            DROP FOREIGN KEY FK_7C1E45A0A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP FOREIGN KEY FK_BCA02E7AA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_resource 
            DROP FOREIGN KEY FK_CBEC498A76ED395
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
            ALTER TABLE claro_api_token 
            DROP FOREIGN KEY FK_2F3470B7A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_admin_tool 
            DROP FOREIGN KEY FK_83338977A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_platform 
            DROP FOREIGN KEY FK_897DE045A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            DROP FOREIGN KEY FK_DDD8A470A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_workspace 
            DROP FOREIGN KEY FK_8724810EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_object_lock 
            DROP FOREIGN KEY FK_9146967CA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_text_revision 
            DROP FOREIGN KEY FK_F61948DEA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCEA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_widget_profile 
            DROP FOREIGN KEY FK_8F55951FA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance_config 
            DROP FOREIGN KEY FK_4787A3FDA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP FOREIGN KEY FK_E0FF6754A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_requirements 
            DROP FOREIGN KEY FK_894BE409A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_facet_role 
            DROP FOREIGN KEY FK_CDD5845DD60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet_role 
            DROP FOREIGN KEY FK_A66BF654D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_user_role 
            DROP FOREIGN KEY FK_797E43FFD60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D9028545248673E9
        ');
        $this->addSql('
            ALTER TABLE claro_group_role 
            DROP FOREIGN KEY FK_1CBA5A40D60322AC
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
            ALTER TABLE claro_workspace_shortcuts 
            DROP FOREIGN KEY FK_872A8149D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            DROP FOREIGN KEY FK_F461C538D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FD60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_role 
            DROP FOREIGN KEY FK_B1EB3A86D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_general_facet_preference 
            DROP FOREIGN KEY FK_38AACF88D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_role_options 
            DROP FOREIGN KEY FK_56C6D283D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP FOREIGN KEY FK_B81359F3D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_requirements 
            DROP FOREIGN KEY FK_894BE409D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_user_administrator 
            DROP FOREIGN KEY FK_22EB9B3A32C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro__organization 
            DROP FOREIGN KEY FK_B68DD0D5727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro__location_organization 
            DROP FOREIGN KEY FK_C4EBDE032C8A3DE
        ');
        $this->addSql('
            ALTER TABLE workspace_organization 
            DROP FOREIGN KEY FK_D212AD8032C8A3DE
        ');
        $this->addSql('
            ALTER TABLE group_organization 
            DROP FOREIGN KEY FK_2DA8294532C8A3DE
        ');
        $this->addSql('
            ALTER TABLE user_organization 
            DROP FOREIGN KEY FK_41221F7EF35E13B7
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            DROP FOREIGN KEY FK_1603A18232C8A3DE
        ');
        $this->addSql('
            ALTER TABLE user_location 
            DROP FOREIGN KEY FK_BE136DCB64D218E
        ');
        $this->addSql('
            ALTER TABLE claro__location_organization 
            DROP FOREIGN KEY FK_C4EBDE064D218E
        ');
        $this->addSql('
            ALTER TABLE group_location 
            DROP FOREIGN KEY FK_57AEC5B464D218E
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_user 
            DROP FOREIGN KEY FK_EB8D285282D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_role 
            DROP FOREIGN KEY FK_3177747182D40A1F
        ');
        $this->addSql('
            ALTER TABLE workspace_organization 
            DROP FOREIGN KEY FK_D212AD8082D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_shortcuts 
            DROP FOREIGN KEY FK_872A814982D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320E82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_options 
            DROP FOREIGN KEY FK_D603AE0582D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            DROP FOREIGN KEY FK_F461C53882D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            DROP FOREIGN KEY FK_DDD8A47082D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_workspace 
            DROP FOREIGN KEY FK_8724810E82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCE82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance_config 
            DROP FOREIGN KEY FK_4787A3FD82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            DROP FOREIGN KEY FK_E0FF675482D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_requirements 
            DROP FOREIGN KEY FK_894BE40982D40A1F
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
            ALTER TABLE group_organization 
            DROP FOREIGN KEY FK_2DA82945FE54D947
        ');
        $this->addSql('
            ALTER TABLE group_location 
            DROP FOREIGN KEY FK_57AEC5B4FE54D947
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FC6F122B2
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tool_role 
            DROP FOREIGN KEY FK_940800692B80F4B6
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_admin_tool 
            DROP FOREIGN KEY FK_833389778F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_list_type_creation 
            DROP FOREIGN KEY FK_84B4BEBA195FBDF1
        ');
        $this->addSql('
            ALTER TABLE claro_tool_rights 
            DROP FOREIGN KEY FK_EFEDEC7EBAC1B1D7
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            DROP FOREIGN KEY FK_DDD8A4708F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D90285453ADB05F1
        ');
        $this->addSql('
            ALTER TABLE claro_template 
            DROP FOREIGN KEY FK_DFB26A757428AC44
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tools 
            DROP FOREIGN KEY FK_C10C14ECEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_template_type 
            DROP FOREIGN KEY FK_7428AC44EC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_resource_type 
            DROP FOREIGN KEY FK_AEC62693EC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action 
            DROP FOREIGN KEY FK_1F57E52BEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_tools 
            DROP FOREIGN KEY FK_60F90965EC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_data_source 
            DROP FOREIGN KEY FK_B4A87F0BEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_widget 
            DROP FOREIGN KEY FK_76CA6C4FEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_user 
            DROP FOREIGN KEY FK_EB8D28523ADB05F1
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF98EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_list_type_creation 
            DROP FOREIGN KEY FK_84B4BEBA98EC6B7B
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
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F98EC6B7B
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
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320E8F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            DROP FOREIGN KEY FK_323623448F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_slide 
            DROP FOREIGN KEY FK_DBB5C281537A1329
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_role 
            DROP FOREIGN KEY FK_B1EB3A86E926F912
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_user 
            DROP FOREIGN KEY FK_6B1166A5E926F912
        ');
        $this->addSql('
            ALTER TABLE claro_public_file_use 
            DROP FOREIGN KEY FK_6F128157C81526DE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            DROP FOREIGN KEY FK_C2A4B1E7FBE9DF40
        ');
        $this->addSql('
            ALTER TABLE claro_activity_parameters 
            DROP FOREIGN KEY FK_E2EE25E281C06096
        ');
        $this->addSql('
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CAC88BD9C1F
        ');
        $this->addSql('
            ALTER TABLE claro_activity_secondary_resources 
            DROP FOREIGN KEY FK_713242A7DB5E3CF7
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
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385F3D3127E
        ');
        $this->addSql('
            ALTER TABLE claro_text_revision 
            DROP FOREIGN KEY FK_F61948DE698D3548
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            DROP FOREIGN KEY FK_F530F6BE7D08FA9E
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container 
            DROP FOREIGN KEY FK_3B06DD75CCE862F
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP FOREIGN KEY FK_B81359F339727CCF
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385FBE885E2
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container_config 
            DROP FOREIGN KEY FK_9523B282581122C3
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385BC21F742
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            DROP FOREIGN KEY FK_57E3C2C6AB7B5A55
        ');
        $this->addSql('
            ALTER TABLE claro_widget_profile 
            DROP FOREIGN KEY FK_8F55951FAB7B5A55
        ');
        $this->addSql('
            ALTER TABLE claro_widget_resource 
            DROP FOREIGN KEY FK_A128E64DAB7B5A55
        ');
        $this->addSql('
            ALTER TABLE claro_widget_simple 
            DROP FOREIGN KEY FK_18CC1F0AAB7B5A55
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance_config 
            DROP FOREIGN KEY FK_4787A3FD44BF891
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_required_resources 
            DROP FOREIGN KEY FK_85A0B2D9296B0ED5
        ');
        $this->addSql('
            DROP TABLE claro_panel_facet
        ');
        $this->addSql('
            DROP TABLE claro_facet
        ');
        $this->addSql('
            DROP TABLE claro_facet_role
        ');
        $this->addSql('
            DROP TABLE claro_field_facet
        ');
        $this->addSql('
            DROP TABLE claro_panel_facet_role
        ');
        $this->addSql('
            DROP TABLE claro_field_facet_choice
        ');
        $this->addSql('
            DROP TABLE claro_resource_node
        ');
        $this->addSql('
            DROP TABLE claro_field_facet_value
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
            DROP TABLE claro_user_administrator
        ');
        $this->addSql('
            DROP TABLE user_location
        ');
        $this->addSql('
            DROP TABLE claro_role
        ');
        $this->addSql('
            DROP TABLE claro__organization
        ');
        $this->addSql('
            DROP TABLE claro__location_organization
        ');
        $this->addSql('
            DROP TABLE claro__location
        ');
        $this->addSql('
            DROP TABLE claro_workspace
        ');
        $this->addSql('
            DROP TABLE workspace_organization
        ');
        $this->addSql('
            DROP TABLE claro_group
        ');
        $this->addSql('
            DROP TABLE claro_group_role
        ');
        $this->addSql('
            DROP TABLE group_organization
        ');
        $this->addSql('
            DROP TABLE group_location
        ');
        $this->addSql('
            DROP TABLE user_organization
        ');
        $this->addSql('
            DROP TABLE claro_cryptographic_key
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
            DROP TABLE claro_workspace_shortcuts
        ');
        $this->addSql('
            DROP TABLE claro_ordered_tool
        ');
        $this->addSql('
            DROP TABLE claro_workspace_options
        ');
        $this->addSql('
            DROP TABLE claro_template_type
        ');
        $this->addSql('
            DROP TABLE claro_plugin
        ');
        $this->addSql('
            DROP TABLE claro_template
        ');
        $this->addSql('
            DROP TABLE claro_user_options
        ');
        $this->addSql('
            DROP TABLE claro_workspace_registration_queue
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
            DROP TABLE claro_log
        ');
        $this->addSql('
            DROP TABLE claro_resource_comment
        ');
        $this->addSql('
            DROP TABLE claro_tool_mask_decoder
        ');
        $this->addSql('
            DROP TABLE claro_tools
        ');
        $this->addSql('
            DROP TABLE claro_content_translation
        ');
        $this->addSql('
            DROP TABLE claro_connection_message_slide
        ');
        $this->addSql('
            DROP TABLE claro_connection_message
        ');
        $this->addSql('
            DROP TABLE claro_connection_message_role
        ');
        $this->addSql('
            DROP TABLE claro_connection_message_user
        ');
        $this->addSql('
            DROP TABLE claro_public_file
        ');
        $this->addSql('
            DROP TABLE claro_directory
        ');
        $this->addSql('
            DROP TABLE claro_file
        ');
        $this->addSql('
            DROP TABLE claro_resource_user_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_resource_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_resource
        ');
        $this->addSql('
            DROP TABLE claro_activity
        ');
        $this->addSql('
            DROP TABLE claro_activity_parameters
        ');
        $this->addSql('
            DROP TABLE claro_activity_secondary_resources
        ');
        $this->addSql('
            DROP TABLE claro_content
        ');
        $this->addSql('
            DROP TABLE claro_activity_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_activity_past_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_api_token
        ');
        $this->addSql('
            DROP TABLE claro_database_backup
        ');
        $this->addSql('
            DROP TABLE claro_data_source
        ');
        $this->addSql('
            DROP TABLE claro_general_facet_preference
        ');
        $this->addSql('
            DROP TABLE claro_public_file_use
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_admin_tool
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_platform
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_tool
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_workspace
        ');
        $this->addSql('
            DROP TABLE claro_object_lock
        ');
        $this->addSql('
            DROP TABLE claro_text_revision
        ');
        $this->addSql('
            DROP TABLE claro_text
        ');
        $this->addSql('
            DROP TABLE claro_role_options
        ');
        $this->addSql('
            DROP TABLE claro_security_token
        ');
        $this->addSql('
            DROP TABLE claro_session
        ');
        $this->addSql('
            DROP TABLE claro_home_tab
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_config
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_roles
        ');
        $this->addSql('
            DROP TABLE claro_version
        ');
        $this->addSql('
            DROP TABLE claro_widget_list
        ');
        $this->addSql('
            DROP TABLE claro_widget_profile
        ');
        $this->addSql('
            DROP TABLE claro_widget_resource
        ');
        $this->addSql('
            DROP TABLE claro_widget_simple
        ');
        $this->addSql('
            DROP TABLE claro_widget
        ');
        $this->addSql('
            DROP TABLE claro_widget_container
        ');
        $this->addSql('
            DROP TABLE claro_widget_container_config
        ');
        $this->addSql('
            DROP TABLE claro_widget_instance
        ');
        $this->addSql('
            DROP TABLE claro_widget_instance_config
        ');
        $this->addSql('
            DROP TABLE claro_workspace_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_workspace_requirements
        ');
        $this->addSql('
            DROP TABLE claro_workspace_required_resources
        ');
    }
}
