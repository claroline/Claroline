<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/20 11:11:55
 */
class Version20150320111153 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_options (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                desktop_background_color VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B2066972A76ED395 ON claro_user_options (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_widget_display_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                widget_instance_id INTEGER NOT NULL, 
                row_position INTEGER NOT NULL, 
                column_position INTEGER NOT NULL, 
                width INTEGER DEFAULT 4 NOT NULL, 
                height INTEGER DEFAULT 3 NOT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_EBBE497282D40A1F ON claro_widget_display_config (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EBBE4972A76ED395 ON claro_widget_display_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EBBE497244BF891 ON claro_widget_display_config (widget_instance_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_display_config_unique_user ON claro_widget_display_config (widget_instance_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_display_config_unique_workspace ON claro_widget_display_config (
                widget_instance_id, workspace_id
            )
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852F85E0677
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D28525126AC48
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D285282D40A1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user AS 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            locale, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri, 
            public_url, 
            has_tuned_public_url, 
            expiration_date, 
            initialization_date, 
            authentication 
            FROM claro_user
        ");
        $this->addSql("
            DROP TABLE claro_user
        ");
        $this->addSql("
            CREATE TABLE claro_user (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                options_id INTEGER DEFAULT NULL, 
                first_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, 
                last_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, 
                username VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                password VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                salt VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                phone VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                mail VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                administrative_code VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                creation_date DATETIME NOT NULL, 
                reset_password VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                hash_time INTEGER DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                description CLOB DEFAULT NULL COLLATE utf8_unicode_ci, 
                locale VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                hasAcceptedTerms BOOLEAN DEFAULT NULL, 
                is_enabled BOOLEAN NOT NULL, 
                is_mail_notified BOOLEAN NOT NULL, 
                last_uri VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                public_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                has_tuned_public_url BOOLEAN NOT NULL, 
                expiration_date DATETIME DEFAULT NULL, 
                initialization_date DATETIME DEFAULT NULL, 
                authentication VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EB8D28523ADB05F1 FOREIGN KEY (options_id) 
                REFERENCES claro_user_options (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user (
                id, workspace_id, first_name, last_name, 
                username, password, salt, phone, mail, 
                administrative_code, creation_date, 
                reset_password, hash_time, picture, 
                description, locale, hasAcceptedTerms, 
                is_enabled, is_mail_notified, last_uri, 
                public_url, has_tuned_public_url, 
                expiration_date, initialization_date, 
                authentication
            ) 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            locale, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri, 
            public_url, 
            has_tuned_public_url, 
            expiration_date, 
            initialization_date, 
            authentication 
            FROM __temp__claro_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user
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
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28523ADB05F1 ON claro_user (options_id)
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_ws_usr
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_name_by_workspace
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E8F7B22CC
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320EA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_ordered_tool AS 
            SELECT id, 
            workspace_id, 
            tool_id, 
            user_id, 
            display_order, 
            name, 
            is_visible_in_desktop 
            FROM claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE claro_ordered_tool
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                tool_id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                display_order INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                is_visible_in_desktop BOOLEAN NOT NULL, 
                ordered_tool_type INTEGER NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6CF1320E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320E8F7B22CC FOREIGN KEY (tool_id) 
                REFERENCES claro_tools (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_ordered_tool (
                id, workspace_id, tool_id, user_id, 
                display_order, name, is_visible_in_desktop
            ) 
            SELECT id, 
            workspace_id, 
            tool_id, 
            user_id, 
            display_order, 
            name, 
            is_visible_in_desktop 
            FROM __temp__claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE __temp__claro_ordered_tool
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name)
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
            CREATE UNIQUE INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool (
                tool_id, user_id, ordered_tool_type
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool (
                tool_id, workspace_id, ordered_tool_type
            )
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD COLUMN default_width INTEGER DEFAULT 4 NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD COLUMN default_height INTEGER DEFAULT 3 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_user_options
        ");
        $this->addSql("
            DROP TABLE claro_widget_display_config
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E8F7B22CC
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320EA76ED395
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_user_type
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_ws_type
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_name_by_workspace
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_ordered_tool AS 
            SELECT id, 
            workspace_id, 
            tool_id, 
            user_id, 
            display_order, 
            name, 
            is_visible_in_desktop 
            FROM claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE claro_ordered_tool
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                tool_id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                display_order INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_visible_in_desktop BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6CF1320E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320E8F7B22CC FOREIGN KEY (tool_id) 
                REFERENCES claro_tools (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6CF1320EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_ordered_tool (
                id, workspace_id, tool_id, user_id, 
                display_order, name, is_visible_in_desktop
            ) 
            SELECT id, 
            workspace_id, 
            tool_id, 
            user_id, 
            display_order, 
            name, 
            is_visible_in_desktop 
            FROM __temp__claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE __temp__claro_ordered_tool
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
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_usr ON claro_ordered_tool (tool_id, workspace_id, user_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852F85E0677
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D28525126AC48
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D285282D40A1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D28523ADB05F1
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user AS 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            locale, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            initialization_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri, 
            public_url, 
            has_tuned_public_url, 
            expiration_date, 
            authentication 
            FROM claro_user
        ");
        $this->addSql("
            DROP TABLE claro_user
        ");
        $this->addSql("
            CREATE TABLE claro_user (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
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
                hash_time INTEGER DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                hasAcceptedTerms BOOLEAN DEFAULT NULL, 
                is_enabled BOOLEAN NOT NULL, 
                is_mail_notified BOOLEAN NOT NULL, 
                last_uri VARCHAR(255) DEFAULT NULL, 
                public_url VARCHAR(255) DEFAULT NULL, 
                has_tuned_public_url BOOLEAN NOT NULL, 
                expiration_date DATETIME DEFAULT NULL, 
                authentication VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user (
                id, workspace_id, first_name, last_name, 
                username, password, locale, salt, 
                phone, mail, administrative_code, 
                creation_date, initialization_date, 
                reset_password, hash_time, picture, 
                description, hasAcceptedTerms, is_enabled, 
                is_mail_notified, last_uri, public_url, 
                has_tuned_public_url, expiration_date, 
                authentication
            ) 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            locale, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            initialization_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri, 
            public_url, 
            has_tuned_public_url, 
            expiration_date, 
            authentication 
            FROM __temp__claro_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852F85E0677 ON claro_user (username)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28525126AC48 ON claro_user (mail)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D285282D40A1F ON claro_user (workspace_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_76CA6C4F5E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_76CA6C4FEC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_widget AS 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            is_exportable, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop 
            FROM claro_widget
        ");
        $this->addSql("
            DROP TABLE claro_widget
        ");
        $this->addSql("
            CREATE TABLE claro_widget (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_configurable BOOLEAN NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                is_displayable_in_workspace BOOLEAN NOT NULL, 
                is_displayable_in_desktop BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_76CA6C4FEC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget (
                id, plugin_id, name, is_configurable, 
                is_exportable, is_displayable_in_workspace, 
                is_displayable_in_desktop
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            is_exportable, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop 
            FROM __temp__claro_widget
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_76CA6C4F5E237E06 ON claro_widget (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_76CA6C4FEC942BCF ON claro_widget (plugin_id)
        ");
    }
}