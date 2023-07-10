<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 09:10:21
 */
final class Version20230602070000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE messenger_messages (
                id BIGINT AUTO_INCREMENT NOT NULL, 
                body LONGTEXT NOT NULL, 
                headers LONGTEXT NOT NULL, 
                queue_name VARCHAR(190) NOT NULL, 
                created_at DATETIME NOT NULL, 
                available_at DATETIME NOT NULL, 
                delivered_at DATETIME DEFAULT NULL, 
                INDEX IDX_75EA56E0FB7336F0 (queue_name), 
                INDEX IDX_75EA56E0E3BD61CE (available_at), 
                INDEX IDX_75EA56E016BA31DB (delivered_at), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            CREATE TABLE claro_user (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
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
                last_activity DATETIME DEFAULT NULL, 
                initialization_date DATETIME DEFAULT NULL, 
                reset_password VARCHAR(255) DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL, 
                hasAcceptedTerms TINYINT(1) NOT NULL, 
                is_enabled TINYINT(1) NOT NULL, 
                is_removed TINYINT(1) NOT NULL, 
                is_locked TINYINT(1) NOT NULL, 
                technical TINYINT(1) DEFAULT 0 NOT NULL, 
                is_mail_notified TINYINT(1) NOT NULL, 
                is_mail_validated TINYINT(1) NOT NULL, 
                expiration_date DATETIME DEFAULT NULL, 
                email_validation_hash VARCHAR(255) DEFAULT NULL, 
                code VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_EB8D2852F85E0677 (username), 
                UNIQUE INDEX UNIQ_EB8D28525126AC48 (mail), 
                UNIQUE INDEX UNIQ_EB8D2852D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_EB8D285282D40A1F (workspace_id), 
                INDEX code_idx (administrative_code), 
                INDEX enabled_idx (is_enabled), 
                INDEX is_removed (is_removed), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_group (
                user_id INT NOT NULL, 
                group_id INT NOT NULL, 
                INDEX IDX_ED8B34C7A76ED395 (user_id), 
                INDEX IDX_ED8B34C7FE54D947 (group_id), 
                PRIMARY KEY(user_id, group_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_role (
                user_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_797E43FFA76ED395 (user_id), 
                INDEX IDX_797E43FFD60322AC (role_id), 
                PRIMARY KEY(user_id, role_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE user_location (
                user_id INT NOT NULL, 
                location_id INT NOT NULL, 
                INDEX IDX_BE136DCBA76ED395 (user_id), 
                INDEX IDX_BE136DCB64D218E (location_id), 
                PRIMARY KEY(user_id, location_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_tools (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                is_displayable_in_workspace TINYINT(1) NOT NULL, 
                is_displayable_in_desktop TINYINT(1) NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_60F90965D17F50A6 (uuid), 
                INDEX IDX_60F90965EC942BCF (plugin_id), 
                UNIQUE INDEX tool_plugin_unique (name, plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_plugin (
                id INT AUTO_INCREMENT NOT NULL, 
                vendor_name VARCHAR(50) NOT NULL, 
                short_name VARCHAR(50) NOT NULL, 
                UNIQUE INDEX plugin_unique_name (vendor_name, short_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_group (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                is_locked TINYINT(1) DEFAULT 0 NOT NULL, 
                UNIQUE INDEX UNIQ_E7C393D75E237E06 (name), 
                UNIQUE INDEX UNIQ_E7C393D7D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_group_role (
                group_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_1CBA5A40FE54D947 (group_id), 
                INDEX IDX_1CBA5A40D60322AC (role_id), 
                PRIMARY KEY(group_id, role_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE group_organization (
                group_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_2DA82945FE54D947 (group_id), 
                INDEX IDX_2DA8294532C8A3DE (organization_id), 
                PRIMARY KEY(group_id, organization_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE group_location (
                group_id INT NOT NULL, 
                location_id INT NOT NULL, 
                INDEX IDX_57AEC5B4FE54D947 (group_id), 
                INDEX IDX_57AEC5B464D218E (location_id), 
                PRIMARY KEY(group_id, location_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_role (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                translation_key VARCHAR(255) NOT NULL, 
                type INT NOT NULL, 
                personal_workspace_creation_enabled TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                is_locked TINYINT(1) DEFAULT 0 NOT NULL, 
                UNIQUE INDEX UNIQ_317774715E237E06 (name), 
                UNIQUE INDEX UNIQ_31777471D17F50A6 (uuid), 
                INDEX IDX_3177747182D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_workspace (
                id INT AUTO_INCREMENT NOT NULL, 
                default_role_id INT DEFAULT NULL, 
                options_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                slug VARCHAR(128) NOT NULL, 
                isModel TINYINT(1) NOT NULL, 
                self_registration TINYINT(1) NOT NULL, 
                registration_validation TINYINT(1) NOT NULL, 
                self_unregistration TINYINT(1) NOT NULL, 
                max_teams INT DEFAULT NULL, 
                is_personal TINYINT(1) NOT NULL, 
                disabled_notifications TINYINT(1) NOT NULL, 
                showProgression TINYINT(1) NOT NULL, 
                contactEmail VARCHAR(255) DEFAULT NULL, 
                successCondition LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                uuid VARCHAR(36) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                archived TINYINT(1) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                updatedAt DATETIME DEFAULT NULL, 
                hidden TINYINT(1) DEFAULT 0 NOT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                access_code VARCHAR(255) DEFAULT NULL, 
                allowed_ips LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                UNIQUE INDEX UNIQ_D9028545989D9B62 (slug), 
                UNIQUE INDEX UNIQ_D9028545D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_D902854577153098 (code), 
                INDEX IDX_D9028545248673E9 (default_role_id), 
                UNIQUE INDEX UNIQ_D90285453ADB05F1 (options_id), 
                INDEX IDX_D902854561220EA6 (creator_id), 
                INDEX name_idx (entity_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE workspace_organization (
                workspace_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_D212AD8082D40A1F (workspace_id), 
                INDEX IDX_D212AD8032C8A3DE (organization_id), 
                PRIMARY KEY(workspace_id, organization_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__location (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                type INT NOT NULL, 
                latitude DOUBLE PRECISION DEFAULT NULL, 
                longitude DOUBLE PRECISION DEFAULT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                address_street1 VARCHAR(255) DEFAULT NULL, 
                address_street2 VARCHAR(255) DEFAULT NULL, 
                address_postal_code VARCHAR(255) DEFAULT NULL, 
                address_city VARCHAR(255) DEFAULT NULL, 
                address_state VARCHAR(255) DEFAULT NULL, 
                address_country VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_24C849F7D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE user_organization (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                is_main TINYINT(1) NOT NULL, 
                is_manager TINYINT(1) NOT NULL, 
                INDEX IDX_41221F7EA76ED395 (user_id), 
                INDEX IDX_41221F7E32C8A3DE (organization_id), 
                UNIQUE INDEX organization_unique_user (user_id, organization_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_admin_tool_role (
                admintool_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_940800692B80F4B6 (admintool_id), 
                INDEX IDX_94080069D60322AC (role_id), 
                PRIMARY KEY(admintool_id, role_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_workspace_shortcuts (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                role_id INT DEFAULT NULL, 
                shortcuts_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_872A8149D17F50A6 (uuid), 
                INDEX IDX_872A814982D40A1F (workspace_id), 
                INDEX IDX_872A8149D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_tool_mask_decoder (
                id INT AUTO_INCREMENT NOT NULL, 
                tool_id INT NOT NULL, 
                value INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_323623448F7B22CC (tool_id), 
                UNIQUE INDEX tool_mask_decoder_unique_tool_and_name (tool_id, name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_ordered_tool (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                tool_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                showIcon TINYINT(1) DEFAULT 0 NOT NULL, 
                fullscreen TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                entity_order INT NOT NULL, 
                hidden TINYINT(1) DEFAULT 0 NOT NULL, 
                UNIQUE INDEX UNIQ_6CF1320ED17F50A6 (uuid), 
                INDEX IDX_6CF1320E82D40A1F (workspace_id), 
                INDEX IDX_6CF1320E8F7B22CC (tool_id), 
                INDEX IDX_6CF1320EA76ED395 (user_id), 
                UNIQUE INDEX ordered_tool_unique_tool_user_type (tool_id, user_id), 
                UNIQUE INDEX ordered_tool_unique_tool_ws_type (tool_id, workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_connection_message (
                id INT AUTO_INCREMENT NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                message_type VARCHAR(255) NOT NULL, 
                locked TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                hidden TINYINT(1) DEFAULT 0 NOT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                UNIQUE INDEX UNIQ_590DE667D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_connection_message_role (
                connectionmessage_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_B1EB3A86E926F912 (connectionmessage_id), 
                INDEX IDX_B1EB3A86D60322AC (role_id), 
                PRIMARY KEY(connectionmessage_id, role_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_connection_message_user (
                connectionmessage_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_6B1166A5E926F912 (connectionmessage_id), 
                INDEX IDX_6B1166A5A76ED395 (user_id), 
                PRIMARY KEY(connectionmessage_id, user_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_connection_message_slide (
                id INT AUTO_INCREMENT NOT NULL, 
                message_id INT DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                slide_order INT NOT NULL, 
                shortcuts LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                uuid VARCHAR(36) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_DBB5C281D17F50A6 (uuid), 
                INDEX IDX_DBB5C281537A1329 (message_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_options (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                breadcrumbItems LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                UNIQUE INDEX UNIQ_D603AE0582D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro__organization (
                id INT AUTO_INCREMENT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                position INT DEFAULT NULL, 
                email VARCHAR(255) DEFAULT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT DEFAULT NULL, 
                is_default TINYINT(1) NOT NULL, 
                is_public TINYINT(1) NOT NULL, 
                vat VARCHAR(255) DEFAULT NULL, 
                type VARCHAR(255) DEFAULT NULL, 
                maxUsers INT NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B68DD0D577153098 (code), 
                UNIQUE INDEX UNIQ_B68DD0D5D17F50A6 (uuid), 
                INDEX IDX_B68DD0D5727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro__location_organization (
                organization_id INT NOT NULL, 
                location_id INT NOT NULL, 
                INDEX IDX_C4EBDE032C8A3DE (organization_id), 
                INDEX IDX_C4EBDE064D218E (location_id), 
                PRIMARY KEY(organization_id, location_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                isRequired TINYINT(1) NOT NULL, 
                options LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                is_metadata TINYINT(1) DEFAULT 0 NOT NULL, 
                locked TINYINT(1) DEFAULT 0 NOT NULL, 
                locked_edition TINYINT(1) DEFAULT 0 NOT NULL, 
                help VARCHAR(255) DEFAULT NULL, 
                condition_field VARCHAR(255) DEFAULT NULL, 
                condition_comparator VARCHAR(255) DEFAULT NULL, 
                condition_value LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                uuid VARCHAR(36) NOT NULL, 
                entity_order INT NOT NULL, 
                panelFacet_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_F6C21DB2D17F50A6 (uuid), 
                INDEX IDX_F6C21DB2E99038C0 (panelFacet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_panel_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                facet_id INT DEFAULT NULL, 
                help VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                icon VARCHAR(255) DEFAULT NULL, 
                entity_order INT NOT NULL, 
                UNIQUE INDEX UNIQ_DA3985FD17F50A6 (uuid), 
                INDEX IDX_DA3985FFC889F24 (facet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_field_facet_value (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                field_value LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                uuid VARCHAR(36) NOT NULL, 
                fieldFacet_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_35307C0AD17F50A6 (uuid), 
                INDEX IDX_35307C0AA76ED395 (user_id), 
                INDEX IDX_35307C0A9F9239AF (fieldFacet_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_resource_type (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(256) NOT NULL, 
                is_exportable TINYINT(1) NOT NULL, 
                tags LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                defaultMask INT NOT NULL, 
                is_enabled TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_AEC626935E237E06 (name), 
                INDEX IDX_AEC62693EC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_menu_action (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT DEFAULT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                decoder VARCHAR(255) NOT NULL, 
                group_name VARCHAR(255) DEFAULT NULL, 
                scope LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                api LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                is_default TINYINT(1) NOT NULL, 
                INDEX IDX_1F57E52B98EC6B7B (resource_type_id), 
                INDEX IDX_1F57E52BEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type_id INT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                license VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                showTitle TINYINT(1) DEFAULT 1 NOT NULL, 
                showIcon TINYINT(1) DEFAULT 1 NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INT DEFAULT NULL, 
                path LONGTEXT DEFAULT NULL, 
                materializedPath LONGTEXT DEFAULT NULL, 
                value INT DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                author VARCHAR(255) DEFAULT NULL, 
                active TINYINT(1) DEFAULT 1 NOT NULL, 
                fullscreen TINYINT(1) NOT NULL, 
                accesses LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                views_count INT DEFAULT 0 NOT NULL, 
                comments_activated TINYINT(1) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                published TINYINT(1) DEFAULT 1 NOT NULL, 
                hidden TINYINT(1) DEFAULT 0 NOT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                evaluated TINYINT(1) DEFAULT 0 NOT NULL, 
                required TINYINT(1) DEFAULT 0 NOT NULL, 
                estimatedDuration INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_A76799FF989D9B62 (slug), 
                UNIQUE INDEX UNIQ_A76799FFD17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_A76799FF77153098 (code), 
                INDEX IDX_A76799FF98EC6B7B (resource_type_id), 
                INDEX IDX_A76799FF727ACA70 (parent_id), 
                INDEX IDX_A76799FF61220EA6 (creator_id), 
                INDEX IDX_A76799FF82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_resource_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_node_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_79F5F969D17F50A6 (uuid), 
                INDEX IDX_79F5F9691BAD783F (resource_node_id), 
                INDEX IDX_79F5F969A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_content_translation (
                id INT AUTO_INCREMENT NOT NULL, 
                locale VARCHAR(8) NOT NULL, 
                object_class VARCHAR(191) NOT NULL, 
                field VARCHAR(32) NOT NULL, 
                foreign_key VARCHAR(64) NOT NULL, 
                content LONGTEXT DEFAULT NULL, 
                INDEX content_translation_idx (
                    locale, object_class, field, foreign_key
                ), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_template (
                id INT AUTO_INCREMENT NOT NULL, 
                claro_template_type INT NOT NULL, 
                is_system TINYINT(1) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DFB26A75D17F50A6 (uuid), 
                INDEX IDX_DFB26A757428AC44 (claro_template_type), 
                UNIQUE INDEX template_unique_name (
                    claro_template_type, entity_name
                ), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_template_type (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                entity_type VARCHAR(255) NOT NULL, 
                placeholders LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                default_template VARCHAR(255) DEFAULT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_7428AC44D17F50A6 (uuid), 
                INDEX IDX_7428AC44EC942BCF (plugin_id), 
                UNIQUE INDEX template_unique_type (entity_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_template_content (
                id INT AUTO_INCREMENT NOT NULL, 
                template_id INT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                lang VARCHAR(255) NOT NULL, 
                INDEX IDX_1D5C077D5DA0FB8 (template_id), 
                UNIQUE INDEX template_unique_lang (template_id, lang), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_planned_object (
                id INT AUTO_INCREMENT NOT NULL, 
                location_id INT DEFAULT NULL, 
                room_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                event_type VARCHAR(255) NOT NULL, 
                event_class VARCHAR(255) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                locationUrl VARCHAR(255) DEFAULT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                updatedAt DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5F6CC1D7D17F50A6 (uuid), 
                INDEX IDX_5F6CC1D764D218E (location_id), 
                INDEX IDX_5F6CC1D754177093 (room_id), 
                INDEX IDX_5F6CC1D761220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                isMain TINYINT(1) NOT NULL, 
                forceCreationForm TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                entity_order INT NOT NULL, 
                icon VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_DCBA6D3AD17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_data_source (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                source_name VARCHAR(255) NOT NULL, 
                source_type VARCHAR(255) NOT NULL, 
                context LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                tags LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_B4A87F0BD17F50A6 (uuid), 
                INDEX IDX_B4A87F0BEC942BCF (plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_public_file (
                id INT AUTO_INCREMENT NOT NULL, 
                file_size INT DEFAULT NULL, 
                filename VARCHAR(255) NOT NULL, 
                hash_name VARCHAR(255) NOT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_7C1E45A0D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_booking_material (
                id INT AUTO_INCREMENT NOT NULL, 
                location_id INT DEFAULT NULL, 
                event_name VARCHAR(255) NOT NULL, 
                capacity INT NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_F7ABA7F577153098 (code), 
                UNIQUE INDEX UNIQ_F7ABA7F5D17F50A6 (uuid), 
                INDEX IDX_F7ABA7F564D218E (location_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_booking_material_booking (
                id INT AUTO_INCREMENT NOT NULL, 
                material_id INT NOT NULL, 
                start_date DATETIME NOT NULL, 
                end_date DATETIME NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_280C960DD17F50A6 (uuid), 
                INDEX IDX_280C960DE308AC6F (material_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_location_room (
                id INT AUTO_INCREMENT NOT NULL, 
                location_id INT DEFAULT NULL, 
                event_name VARCHAR(255) NOT NULL, 
                capacity INT NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DFA335DB77153098 (code), 
                UNIQUE INDEX UNIQ_DFA335DBD17F50A6 (uuid), 
                INDEX IDX_DFA335DB64D218E (location_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_booking_room_booking (
                id INT AUTO_INCREMENT NOT NULL, 
                room_id INT NOT NULL, 
                start_date DATETIME NOT NULL, 
                end_date DATETIME NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_F4DAFBDFD17F50A6 (uuid), 
                INDEX IDX_F4DAFBDF54177093 (room_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
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
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_planning (
                id INT AUTO_INCREMENT NOT NULL, 
                objectId VARCHAR(255) NOT NULL, 
                objectClass VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_9C4BCA00D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_planning_planned_object (
                planning_id INT NOT NULL, 
                planned_object_id INT NOT NULL, 
                INDEX IDX_A05487943D865311 (planning_id), 
                INDEX IDX_A0548794A669922F (planned_object_id), 
                PRIMARY KEY(planning_id, planned_object_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_directory (
                id INT AUTO_INCREMENT NOT NULL, 
                is_upload_destination TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                filterable TINYINT(1) NOT NULL, 
                sortable TINYINT(1) NOT NULL, 
                paginated TINYINT(1) NOT NULL, 
                columnsFilterable TINYINT(1) NOT NULL, 
                count TINYINT(1) NOT NULL, 
                actions TINYINT(1) NOT NULL, 
                sortBy VARCHAR(255) DEFAULT NULL, 
                availableSort LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                pageSize INT NOT NULL, 
                availablePageSizes LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                display VARCHAR(255) NOT NULL, 
                availableDisplays LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                searchMode VARCHAR(255) DEFAULT NULL, 
                filters LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                availableFilters LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                availableColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                displayedColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                card LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_12EEC186D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_12EEC186B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_file (
                id INT AUTO_INCREMENT NOT NULL, 
                size INT NOT NULL, 
                hash_name VARCHAR(255) NOT NULL, 
                opening VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_EA81C80BD17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_EA81C80BB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_resource_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_user_evaluation INT DEFAULT NULL, 
                evaluation_comment LONGTEXT DEFAULT NULL, 
                more_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_status VARCHAR(255) NOT NULL, 
                duration INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                score_min DOUBLE PRECISION DEFAULT NULL, 
                score_max DOUBLE PRECISION DEFAULT NULL, 
                progression INT NOT NULL, 
                INDEX IDX_C2A4B1E7FBE9DF40 (resource_user_evaluation), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_resource_user_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_node INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                nb_attempts INT NOT NULL, 
                nb_openings INT NOT NULL, 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_status VARCHAR(255) NOT NULL, 
                duration INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                score_min DOUBLE PRECISION DEFAULT NULL, 
                score_max DOUBLE PRECISION DEFAULT NULL, 
                progression INT NOT NULL, 
                INDEX IDX_BCA02E7A8A5F48FF (resource_node), 
                INDEX IDX_BCA02E7AA76ED395 (user_id), 
                UNIQUE INDEX resource_user_evaluation (resource_node, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_text (
                id INT AUTO_INCREMENT NOT NULL, 
                version INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5D9559DCD17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_5D9559DCB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_update (
                id INT AUTO_INCREMENT NOT NULL, 
                updater_class VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_6B1647E7CACF2BB1 (updater_class), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
                availableSort LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                pageSize INT NOT NULL, 
                availablePageSizes LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                display VARCHAR(255) NOT NULL, 
                availableDisplays LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                searchMode VARCHAR(255) DEFAULT NULL, 
                filters LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                availableFilters LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                availableColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                displayedColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                card LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_57E3C2C6AB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_widget_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                node_id INT DEFAULT NULL, 
                showResourceHeader TINYINT(1) NOT NULL, 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_A128E64DAB7B5A55 (widgetInstance_id), 
                INDEX IDX_A128E64D460D9FD7 (node_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_widget_simple (
                id INT AUTO_INCREMENT NOT NULL, 
                content LONGTEXT NOT NULL, 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_18CC1F0AAB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_widget (
                id INT AUTO_INCREMENT NOT NULL, 
                plugin_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                class VARCHAR(255) DEFAULT NULL, 
                sources LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                context LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                is_exportable TINYINT(1) NOT NULL, 
                tags LONGTEXT NOT NULL COMMENT '(DC2Type:json)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_76CA6C4FD17F50A6 (uuid), 
                INDEX IDX_76CA6C4FEC942BCF (plugin_id), 
                UNIQUE INDEX widget_plugin_unique (name, plugin_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_widget_container (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_3B06DD75D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_widget_container_config (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_container_id INT DEFAULT NULL, 
                widget_name VARCHAR(255) DEFAULT NULL, 
                alignName VARCHAR(255) NOT NULL, 
                is_visible TINYINT(1) NOT NULL, 
                layout LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                color VARCHAR(255) DEFAULT NULL, 
                borderColor VARCHAR(255) DEFAULT NULL, 
                backgroundType VARCHAR(255) NOT NULL, 
                background VARCHAR(255) DEFAULT NULL, 
                position INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_9523B282D17F50A6 (uuid), 
                INDEX IDX_9523B282581122C3 (widget_container_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_status VARCHAR(255) NOT NULL, 
                duration INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                score_min DOUBLE PRECISION DEFAULT NULL, 
                score_max DOUBLE PRECISION DEFAULT NULL, 
                progression INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_E0FF6754D17F50A6 (uuid), 
                INDEX IDX_E0FF675482D40A1F (workspace_id), 
                INDEX IDX_E0FF6754A76ED395 (user_id), 
                UNIQUE INDEX workspace_user_evaluation (workspace_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
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
            ALTER TABLE claro_tools 
            ADD CONSTRAINT FK_60F90965EC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
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
            ALTER TABLE claro_role 
            ADD CONSTRAINT FK_3177747182D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
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
            ALTER TABLE user_organization 
            ADD CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE user_organization 
            ADD CONSTRAINT FK_41221F7E32C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
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
            ALTER TABLE claro_tool_mask_decoder 
            ADD CONSTRAINT FK_323623448F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) 
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
            ALTER TABLE claro_connection_message_slide 
            ADD CONSTRAINT FK_DBB5C281537A1329 FOREIGN KEY (message_id) 
            REFERENCES claro_connection_message (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_options 
            ADD CONSTRAINT FK_D603AE0582D40A1F FOREIGN KEY (workspace_id) 
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
            ALTER TABLE claro_cryptographic_key 
            ADD CONSTRAINT FK_1603A182A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            ADD CONSTRAINT FK_1603A18232C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2E99038C0 FOREIGN KEY (panelFacet_id) 
            REFERENCES claro_panel_facet (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            ADD CONSTRAINT FK_DA3985FFC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
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
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_comment 
            ADD CONSTRAINT FK_79F5F9691BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_comment 
            ADD CONSTRAINT FK_79F5F969A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_template 
            ADD CONSTRAINT FK_DFB26A757428AC44 FOREIGN KEY (claro_template_type) 
            REFERENCES claro_template_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_template_type 
            ADD CONSTRAINT FK_7428AC44EC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_template_content 
            ADD CONSTRAINT FK_1D5C077D5DA0FB8 FOREIGN KEY (template_id) 
            REFERENCES claro_template (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            ADD CONSTRAINT FK_5F6CC1D764D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            ADD CONSTRAINT FK_5F6CC1D754177093 FOREIGN KEY (room_id) 
            REFERENCES claro_location_room (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            ADD CONSTRAINT FK_5F6CC1D761220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_data_source 
            ADD CONSTRAINT FK_B4A87F0BEC942BCF FOREIGN KEY (plugin_id) 
            REFERENCES claro_plugin (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_public_file_use 
            ADD CONSTRAINT FK_6F128157C81526DE FOREIGN KEY (public_file_id) 
            REFERENCES claro_public_file (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_booking_material 
            ADD CONSTRAINT FK_F7ABA7F564D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_booking_material_booking 
            ADD CONSTRAINT FK_280C960DE308AC6F FOREIGN KEY (material_id) 
            REFERENCES claro_booking_material (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_location_room 
            ADD CONSTRAINT FK_DFA335DB64D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_booking_room_booking 
            ADD CONSTRAINT FK_F4DAFBDF54177093 FOREIGN KEY (room_id) 
            REFERENCES claro_location_room (id) 
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
            ALTER TABLE claro_object_lock 
            ADD CONSTRAINT FK_9146967CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_planning_planned_object 
            ADD CONSTRAINT FK_A05487943D865311 FOREIGN KEY (planning_id) 
            REFERENCES claro_planning (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_planning_planned_object 
            ADD CONSTRAINT FK_A0548794A669922F FOREIGN KEY (planned_object_id) 
            REFERENCES claro_planned_object (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_resource_evaluation 
            ADD CONSTRAINT FK_C2A4B1E7FBE9DF40 FOREIGN KEY (resource_user_evaluation) 
            REFERENCES claro_resource_user_evaluation (id) 
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
            ALTER TABLE claro_widget_list 
            ADD CONSTRAINT FK_57E3C2C6AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
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
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD CONSTRAINT FK_E0FF6754A76ED395 FOREIGN KEY (user_id) 
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
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_user 
            DROP FOREIGN KEY FK_EB8D285282D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_user_group 
            DROP FOREIGN KEY FK_ED8B34C7A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_user_group 
            DROP FOREIGN KEY FK_ED8B34C7FE54D947
        ');
        $this->addSql('
            ALTER TABLE claro_user_role 
            DROP FOREIGN KEY FK_797E43FFA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_user_role 
            DROP FOREIGN KEY FK_797E43FFD60322AC
        ');
        $this->addSql('
            ALTER TABLE user_location 
            DROP FOREIGN KEY FK_BE136DCBA76ED395
        ');
        $this->addSql('
            ALTER TABLE user_location 
            DROP FOREIGN KEY FK_BE136DCB64D218E
        ');
        $this->addSql('
            ALTER TABLE claro_tools 
            DROP FOREIGN KEY FK_60F90965EC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_group_role 
            DROP FOREIGN KEY FK_1CBA5A40FE54D947
        ');
        $this->addSql('
            ALTER TABLE claro_group_role 
            DROP FOREIGN KEY FK_1CBA5A40D60322AC
        ');
        $this->addSql('
            ALTER TABLE group_organization 
            DROP FOREIGN KEY FK_2DA82945FE54D947
        ');
        $this->addSql('
            ALTER TABLE group_organization 
            DROP FOREIGN KEY FK_2DA8294532C8A3DE
        ');
        $this->addSql('
            ALTER TABLE group_location 
            DROP FOREIGN KEY FK_57AEC5B4FE54D947
        ');
        $this->addSql('
            ALTER TABLE group_location 
            DROP FOREIGN KEY FK_57AEC5B464D218E
        ');
        $this->addSql('
            ALTER TABLE claro_role 
            DROP FOREIGN KEY FK_3177747182D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D9028545248673E9
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D90285453ADB05F1
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D902854561220EA6
        ');
        $this->addSql('
            ALTER TABLE workspace_organization 
            DROP FOREIGN KEY FK_D212AD8082D40A1F
        ');
        $this->addSql('
            ALTER TABLE workspace_organization 
            DROP FOREIGN KEY FK_D212AD8032C8A3DE
        ');
        $this->addSql('
            ALTER TABLE user_organization 
            DROP FOREIGN KEY FK_41221F7EA76ED395
        ');
        $this->addSql('
            ALTER TABLE user_organization 
            DROP FOREIGN KEY FK_41221F7E32C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tools 
            DROP FOREIGN KEY FK_C10C14ECEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tool_role 
            DROP FOREIGN KEY FK_940800692B80F4B6
        ');
        $this->addSql('
            ALTER TABLE claro_admin_tool_role 
            DROP FOREIGN KEY FK_94080069D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_tool_rights 
            DROP FOREIGN KEY FK_EFEDEC7ED60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_tool_rights 
            DROP FOREIGN KEY FK_EFEDEC7EBAC1B1D7
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_shortcuts 
            DROP FOREIGN KEY FK_872A814982D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_shortcuts 
            DROP FOREIGN KEY FK_872A8149D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            DROP FOREIGN KEY FK_323623448F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320E82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320E8F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320EA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_role 
            DROP FOREIGN KEY FK_B1EB3A86E926F912
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_role 
            DROP FOREIGN KEY FK_B1EB3A86D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_user 
            DROP FOREIGN KEY FK_6B1166A5E926F912
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_user 
            DROP FOREIGN KEY FK_6B1166A5A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_slide 
            DROP FOREIGN KEY FK_DBB5C281537A1329
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_options 
            DROP FOREIGN KEY FK_D603AE0582D40A1F
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
            ALTER TABLE claro__location_organization 
            DROP FOREIGN KEY FK_C4EBDE064D218E
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            DROP FOREIGN KEY FK_1603A182A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_cryptographic_key 
            DROP FOREIGN KEY FK_1603A18232C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP FOREIGN KEY FK_F6C21DB2E99038C0
        ');
        $this->addSql('
            ALTER TABLE claro_panel_facet 
            DROP FOREIGN KEY FK_DA3985FFC889F24
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            DROP FOREIGN KEY FK_E2001D9F9239AF
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_choice 
            DROP FOREIGN KEY FK_E2001D727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            DROP FOREIGN KEY FK_35307C0AA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            DROP FOREIGN KEY FK_35307C0A9F9239AF
        ');
        $this->addSql('
            ALTER TABLE claro_resource_mask_decoder 
            DROP FOREIGN KEY FK_39D93F4298EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_resource_type 
            DROP FOREIGN KEY FK_AEC62693EC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action 
            DROP FOREIGN KEY FK_1F57E52B98EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action 
            DROP FOREIGN KEY FK_1F57E52BEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F483D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_resource_rights 
            DROP FOREIGN KEY FK_3848F483B87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_list_type_creation 
            DROP FOREIGN KEY FK_84B4BEBA195FBDF1
        ');
        $this->addSql('
            ALTER TABLE claro_list_type_creation 
            DROP FOREIGN KEY FK_84B4BEBA98EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF98EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF61220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_resource_comment 
            DROP FOREIGN KEY FK_79F5F9691BAD783F
        ');
        $this->addSql('
            ALTER TABLE claro_resource_comment 
            DROP FOREIGN KEY FK_79F5F969A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_template 
            DROP FOREIGN KEY FK_DFB26A757428AC44
        ');
        $this->addSql('
            ALTER TABLE claro_template_type 
            DROP FOREIGN KEY FK_7428AC44EC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_template_content 
            DROP FOREIGN KEY FK_1D5C077D5DA0FB8
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            DROP FOREIGN KEY FK_5F6CC1D764D218E
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            DROP FOREIGN KEY FK_5F6CC1D754177093
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            DROP FOREIGN KEY FK_5F6CC1D761220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_data_source 
            DROP FOREIGN KEY FK_B4A87F0BEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_public_file_use 
            DROP FOREIGN KEY FK_6F128157C81526DE
        ');
        $this->addSql('
            ALTER TABLE claro_booking_material 
            DROP FOREIGN KEY FK_F7ABA7F564D218E
        ');
        $this->addSql('
            ALTER TABLE claro_booking_material_booking 
            DROP FOREIGN KEY FK_280C960DE308AC6F
        ');
        $this->addSql('
            ALTER TABLE claro_location_room 
            DROP FOREIGN KEY FK_DFA335DB64D218E
        ');
        $this->addSql('
            ALTER TABLE claro_booking_room_booking 
            DROP FOREIGN KEY FK_F4DAFBDF54177093
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_admin_tool 
            DROP FOREIGN KEY FK_833389778F7B22CC
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
            ALTER TABLE claro_log_connect_resource 
            DROP FOREIGN KEY FK_CBEC49889329D25
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_resource 
            DROP FOREIGN KEY FK_CBEC498A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            DROP FOREIGN KEY FK_DDD8A4708F7B22CC
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            DROP FOREIGN KEY FK_DDD8A47082D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_tool 
            DROP FOREIGN KEY FK_DDD8A470A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_workspace 
            DROP FOREIGN KEY FK_8724810E82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_log_connect_workspace 
            DROP FOREIGN KEY FK_8724810EA76ED395
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
            DROP FOREIGN KEY FK_97FAB91FC6F122B2
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91F98EC6B7B
        ');
        $this->addSql('
            ALTER TABLE claro_log 
            DROP FOREIGN KEY FK_97FAB91FD60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_object_lock 
            DROP FOREIGN KEY FK_9146967CA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_planning_planned_object 
            DROP FOREIGN KEY FK_A05487943D865311
        ');
        $this->addSql('
            ALTER TABLE claro_planning_planned_object 
            DROP FOREIGN KEY FK_A0548794A669922F
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
            ALTER TABLE claro_resource_evaluation 
            DROP FOREIGN KEY FK_C2A4B1E7FBE9DF40
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP FOREIGN KEY FK_BCA02E7A8A5F48FF
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            DROP FOREIGN KEY FK_BCA02E7AA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_text_revision 
            DROP FOREIGN KEY FK_F61948DE698D3548
        ');
        $this->addSql('
            ALTER TABLE claro_text_revision 
            DROP FOREIGN KEY FK_F61948DEA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_text 
            DROP FOREIGN KEY FK_5D9559DCB87FAB32
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            DROP FOREIGN KEY FK_57E3C2C6AB7B5A55
        ');
        $this->addSql('
            ALTER TABLE claro_widget_resource 
            DROP FOREIGN KEY FK_A128E64DAB7B5A55
        ');
        $this->addSql('
            ALTER TABLE claro_widget_resource 
            DROP FOREIGN KEY FK_A128E64D460D9FD7
        ');
        $this->addSql('
            ALTER TABLE claro_widget_simple 
            DROP FOREIGN KEY FK_18CC1F0AAB7B5A55
        ');
        $this->addSql('
            ALTER TABLE claro_widget 
            DROP FOREIGN KEY FK_76CA6C4FEC942BCF
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container_config 
            DROP FOREIGN KEY FK_9523B282581122C3
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385FBE885E2
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385BC21F742
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance 
            DROP FOREIGN KEY FK_5F89A385F3D3127E
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance_config 
            DROP FOREIGN KEY FK_4787A3FD44BF891
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance_config 
            DROP FOREIGN KEY FK_4787A3FDA76ED395
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
            ALTER TABLE claro_workspace_evaluation 
            DROP FOREIGN KEY FK_E0FF6754A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            DROP FOREIGN KEY FK_F461C538D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            DROP FOREIGN KEY FK_F461C538A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_registration_queue 
            DROP FOREIGN KEY FK_F461C53882D40A1F
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
            DROP TABLE user_location
        ');
        $this->addSql('
            DROP TABLE claro_tools
        ');
        $this->addSql('
            DROP TABLE claro_plugin
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
            DROP TABLE claro_role
        ');
        $this->addSql('
            DROP TABLE claro_workspace
        ');
        $this->addSql('
            DROP TABLE workspace_organization
        ');
        $this->addSql('
            DROP TABLE claro__location
        ');
        $this->addSql('
            DROP TABLE user_organization
        ');
        $this->addSql('
            DROP TABLE claro_admin_tools
        ');
        $this->addSql('
            DROP TABLE claro_admin_tool_role
        ');
        $this->addSql('
            DROP TABLE claro_tool_rights
        ');
        $this->addSql('
            DROP TABLE claro_workspace_shortcuts
        ');
        $this->addSql('
            DROP TABLE claro_tool_mask_decoder
        ');
        $this->addSql('
            DROP TABLE claro_ordered_tool
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
            DROP TABLE claro_connection_message_slide
        ');
        $this->addSql('
            DROP TABLE claro_workspace_options
        ');
        $this->addSql('
            DROP TABLE claro__organization
        ');
        $this->addSql('
            DROP TABLE claro__location_organization
        ');
        $this->addSql('
            DROP TABLE claro_cryptographic_key
        ');
        $this->addSql('
            DROP TABLE claro_field_facet
        ');
        $this->addSql('
            DROP TABLE claro_panel_facet
        ');
        $this->addSql('
            DROP TABLE claro_field_facet_choice
        ');
        $this->addSql('
            DROP TABLE claro_field_facet_value
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
            DROP TABLE claro_resource_rights
        ');
        $this->addSql('
            DROP TABLE claro_list_type_creation
        ');
        $this->addSql('
            DROP TABLE claro_resource_node
        ');
        $this->addSql('
            DROP TABLE claro_resource_comment
        ');
        $this->addSql('
            DROP TABLE claro_content_translation
        ');
        $this->addSql('
            DROP TABLE claro_template
        ');
        $this->addSql('
            DROP TABLE claro_template_type
        ');
        $this->addSql('
            DROP TABLE claro_template_content
        ');
        $this->addSql('
            DROP TABLE claro_planned_object
        ');
        $this->addSql('
            DROP TABLE claro_facet
        ');
        $this->addSql('
            DROP TABLE claro_content
        ');
        $this->addSql('
            DROP TABLE claro_data_source
        ');
        $this->addSql('
            DROP TABLE claro_public_file
        ');
        $this->addSql('
            DROP TABLE claro_public_file_use
        ');
        $this->addSql('
            DROP TABLE claro_booking_material
        ');
        $this->addSql('
            DROP TABLE claro_booking_material_booking
        ');
        $this->addSql('
            DROP TABLE claro_location_room
        ');
        $this->addSql('
            DROP TABLE claro_booking_room_booking
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_admin_tool
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_platform
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_resource
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_tool
        ');
        $this->addSql('
            DROP TABLE claro_log_connect_workspace
        ');
        $this->addSql('
            DROP TABLE claro_log
        ');
        $this->addSql('
            DROP TABLE claro_object_lock
        ');
        $this->addSql('
            DROP TABLE claro_planning
        ');
        $this->addSql('
            DROP TABLE claro_planning_planned_object
        ');
        $this->addSql('
            DROP TABLE claro_directory
        ');
        $this->addSql('
            DROP TABLE claro_file
        ');
        $this->addSql('
            DROP TABLE claro_resource_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_resource_user_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_text_revision
        ');
        $this->addSql('
            DROP TABLE claro_text
        ');
        $this->addSql('
            DROP TABLE claro_update
        ');
        $this->addSql('
            DROP TABLE claro_version
        ');
        $this->addSql('
            DROP TABLE claro_widget_list
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
            DROP TABLE claro_workspace_registration_queue
        ');
    }
}
