<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/07/30 09:10:30
 */
class Version20130730091030 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_4A98967B98EC6B7B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_type_custom_action AS 
            SELECT id, 
            resource_type_id, 
            \"action\", 
            async 
            FROM claro_resource_type_custom_action
        ");
        $this->addSql("
            DROP TABLE claro_resource_type_custom_action
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type_custom_action (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                \"action\" VARCHAR(255) DEFAULT NULL, 
                async BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_4A98967B98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_type_custom_action (
                id, resource_type_id, \"action\", async
            ) 
            SELECT id, 
            resource_type_id, 
            \"action\", 
            async 
            FROM __temp__claro_resource_type_custom_action
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_type_custom_action
        ");
        $this->addSql("
            CREATE INDEX IDX_4A98967B98EC6B7B ON claro_resource_type_custom_action (resource_type_id)
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
                resource_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5E7F4AB889329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_5E7F4AB8BF396750 FOREIGN KEY (id) 
                REFERENCES claro_resource (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_shortcut (id, resource_id) 
            SELECT id, 
            resource_id 
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB889329D25 ON claro_resource_shortcut (resource_id)
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
            DROP INDEX unique_tool_name
        ");
        $this->addSql("
            DROP INDEX IDX_60F90965EC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_tools AS 
            SELECT id, 
            plugin_id, 
            name, 
            display_name, 
            class, 
            is_workspace_required, 
            is_desktop_required, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop, 
            is_exportable, 
            has_options 
            FROM claro_tools
        ");
        $this->addSql("
            DROP TABLE claro_tools
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_60F90965EC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_tools (
                id, plugin_id, name, display_name, 
                class, is_workspace_required, is_desktop_required, 
                is_displayable_in_workspace, is_displayable_in_desktop, 
                is_exportable, has_options
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            display_name, 
            class, 
            is_workspace_required, 
            is_desktop_required, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop, 
            is_exportable, 
            has_options 
            FROM __temp__claro_tools
        ");
        $this->addSql("
            DROP TABLE __temp__claro_tools
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_tool_name ON claro_tools (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_60F90965EC942BCF ON claro_tools (plugin_id)
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB382D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3FBE885E2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_widget_display AS 
            SELECT id, 
            parent_id, 
            workspace_id, 
            user_id, 
            widget_id, 
            is_locked, 
            is_visible, 
            is_desktop 
            FROM claro_widget_display
        ");
        $this->addSql("
            DROP TABLE claro_widget_display
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_2D34DB3727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_widget_display (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_2D34DB382D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_2D34DB3A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_2D34DB3FBE885E2 FOREIGN KEY (widget_id) 
                REFERENCES claro_widget (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget_display (
                id, parent_id, workspace_id, user_id, 
                widget_id, is_locked, is_visible, 
                is_desktop
            ) 
            SELECT id, 
            parent_id, 
            workspace_id, 
            user_id, 
            widget_id, 
            is_locked, 
            is_visible, 
            is_desktop 
            FROM __temp__claro_widget_display
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget_display
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
            DROP INDEX tool
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
            icon, 
            is_exportable 
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
                icon VARCHAR(255) NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_76CA6C4FEC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget (
                id, plugin_id, name, is_configurable, 
                icon, is_exportable
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            icon, 
            is_exportable 
            FROM __temp__claro_widget
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tool ON claro_widget (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_76CA6C4FEC942BCF ON claro_widget (plugin_id)
        ");
        $this->addSql("
            DROP INDEX IDX_8D18942E84A0A3ED
        ");
        $this->addSql("
            DROP INDEX IDX_8D18942E98260155
        ");
        $this->addSql("
            DROP INDEX IDX_8D18942EAA23F6C8
        ");
        $this->addSql("
            DROP INDEX IDX_8D18942EE9583FF0
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_content2region AS 
            SELECT id, 
            content_id, 
            region_id, 
            next_id, 
            back_id, 
            size 
            FROM claro_content2region
        ");
        $this->addSql("
            DROP TABLE claro_content2region
        ");
        $this->addSql("
            CREATE TABLE claro_content2region (
                id INTEGER NOT NULL, 
                content_id INTEGER NOT NULL, 
                region_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                back_id INTEGER DEFAULT NULL, 
                size VARCHAR(30) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_8D18942E84A0A3ED FOREIGN KEY (content_id) 
                REFERENCES claro_content (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_8D18942E98260155 FOREIGN KEY (region_id) 
                REFERENCES claro_region (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_8D18942EAA23F6C8 FOREIGN KEY (next_id) 
                REFERENCES claro_content2region (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_8D18942EE9583FF0 FOREIGN KEY (back_id) 
                REFERENCES claro_content2region (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_content2region (
                id, content_id, region_id, next_id, 
                back_id, size
            ) 
            SELECT id, 
            content_id, 
            region_id, 
            next_id, 
            back_id, 
            size 
            FROM __temp__claro_content2region
        ");
        $this->addSql("
            DROP TABLE __temp__claro_content2region
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
            DROP INDEX IDX_1A2084EF84A0A3ED
        ");
        $this->addSql("
            DROP INDEX IDX_1A2084EFC54C8C93
        ");
        $this->addSql("
            DROP INDEX IDX_1A2084EFAA23F6C8
        ");
        $this->addSql("
            DROP INDEX IDX_1A2084EFE9583FF0
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_content2type AS 
            SELECT id, 
            content_id, 
            type_id, 
            next_id, 
            back_id, 
            size 
            FROM claro_content2type
        ");
        $this->addSql("
            DROP TABLE claro_content2type
        ");
        $this->addSql("
            CREATE TABLE claro_content2type (
                id INTEGER NOT NULL, 
                content_id INTEGER NOT NULL, 
                type_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                back_id INTEGER DEFAULT NULL, 
                size VARCHAR(30) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1A2084EF84A0A3ED FOREIGN KEY (content_id) 
                REFERENCES claro_content (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1A2084EFC54C8C93 FOREIGN KEY (type_id) 
                REFERENCES claro_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1A2084EFAA23F6C8 FOREIGN KEY (next_id) 
                REFERENCES claro_content2type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1A2084EFE9583FF0 FOREIGN KEY (back_id) 
                REFERENCES claro_content2type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_content2type (
                id, content_id, type_id, next_id, back_id, 
                size
            ) 
            SELECT id, 
            content_id, 
            type_id, 
            next_id, 
            back_id, 
            size 
            FROM __temp__claro_content2type
        ");
        $this->addSql("
            DROP TABLE __temp__claro_content2type
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
            DROP INDEX IDX_D72E133C2055B9A2
        ");
        $this->addSql("
            DROP INDEX IDX_D72E133CDD62C21B
        ");
        $this->addSql("
            DROP INDEX IDX_D72E133CAA23F6C8
        ");
        $this->addSql("
            DROP INDEX IDX_D72E133CE9583FF0
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_subcontent AS 
            SELECT id, 
            father_id, 
            child_id, 
            next_id, 
            back_id, 
            size 
            FROM claro_subcontent
        ");
        $this->addSql("
            DROP TABLE claro_subcontent
        ");
        $this->addSql("
            CREATE TABLE claro_subcontent (
                id INTEGER NOT NULL, 
                father_id INTEGER NOT NULL, 
                child_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                back_id INTEGER DEFAULT NULL, 
                size VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D72E133C2055B9A2 FOREIGN KEY (father_id) 
                REFERENCES claro_content (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D72E133CDD62C21B FOREIGN KEY (child_id) 
                REFERENCES claro_content (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D72E133CAA23F6C8 FOREIGN KEY (next_id) 
                REFERENCES claro_subcontent (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D72E133CE9583FF0 FOREIGN KEY (back_id) 
                REFERENCES claro_subcontent (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_subcontent (
                id, father_id, child_id, next_id, back_id, 
                size
            ) 
            SELECT id, 
            father_id, 
            child_id, 
            next_id, 
            back_id, 
            size 
            FROM __temp__claro_subcontent
        ");
        $this->addSql("
            DROP TABLE __temp__claro_subcontent
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
    }

    public function down(Schema $schema)
    {
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
                PRIMARY KEY(id)
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
            DROP INDEX IDX_8D18942E84A0A3ED
        ");
        $this->addSql("
            DROP INDEX IDX_8D18942E98260155
        ");
        $this->addSql("
            DROP INDEX IDX_8D18942EAA23F6C8
        ");
        $this->addSql("
            DROP INDEX IDX_8D18942EE9583FF0
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_content2region AS 
            SELECT id, 
            content_id, 
            region_id, 
            next_id, 
            back_id, 
            size 
            FROM claro_content2region
        ");
        $this->addSql("
            DROP TABLE claro_content2region
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
            INSERT INTO claro_content2region (
                id, content_id, region_id, next_id, 
                back_id, size
            ) 
            SELECT id, 
            content_id, 
            region_id, 
            next_id, 
            back_id, 
            size 
            FROM __temp__claro_content2region
        ");
        $this->addSql("
            DROP TABLE __temp__claro_content2region
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
            DROP INDEX IDX_1A2084EF84A0A3ED
        ");
        $this->addSql("
            DROP INDEX IDX_1A2084EFC54C8C93
        ");
        $this->addSql("
            DROP INDEX IDX_1A2084EFAA23F6C8
        ");
        $this->addSql("
            DROP INDEX IDX_1A2084EFE9583FF0
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_content2type AS 
            SELECT id, 
            content_id, 
            type_id, 
            next_id, 
            back_id, 
            size 
            FROM claro_content2type
        ");
        $this->addSql("
            DROP TABLE claro_content2type
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
            INSERT INTO claro_content2type (
                id, content_id, type_id, next_id, back_id, 
                size
            ) 
            SELECT id, 
            content_id, 
            type_id, 
            next_id, 
            back_id, 
            size 
            FROM __temp__claro_content2type
        ");
        $this->addSql("
            DROP TABLE __temp__claro_content2type
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
                resource_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_shortcut (id, resource_id) 
            SELECT id, 
            resource_id 
            FROM __temp__claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_shortcut
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB889329D25 ON claro_resource_shortcut (resource_id)
        ");
        $this->addSql("
            DROP INDEX IDX_4A98967B98EC6B7B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_type_custom_action AS 
            SELECT id, 
            resource_type_id, 
            \"action\", 
            async 
            FROM claro_resource_type_custom_action
        ");
        $this->addSql("
            DROP TABLE claro_resource_type_custom_action
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type_custom_action (
                id INTEGER NOT NULL, 
                \"action\" VARCHAR(255) DEFAULT NULL, 
                async BOOLEAN DEFAULT NULL, 
                resource_type_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_type_custom_action (
                id, resource_type_id, \"action\", async
            ) 
            SELECT id, 
            resource_type_id, 
            \"action\", 
            async 
            FROM __temp__claro_resource_type_custom_action
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_type_custom_action
        ");
        $this->addSql("
            CREATE INDEX IDX_4A98967B98EC6B7B ON claro_resource_type_custom_action (resource_type_id)
        ");
        $this->addSql("
            DROP INDEX IDX_D72E133C2055B9A2
        ");
        $this->addSql("
            DROP INDEX IDX_D72E133CDD62C21B
        ");
        $this->addSql("
            DROP INDEX IDX_D72E133CAA23F6C8
        ");
        $this->addSql("
            DROP INDEX IDX_D72E133CE9583FF0
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_subcontent AS 
            SELECT id, 
            father_id, 
            child_id, 
            next_id, 
            back_id, 
            size 
            FROM claro_subcontent
        ");
        $this->addSql("
            DROP TABLE claro_subcontent
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
            INSERT INTO claro_subcontent (
                id, father_id, child_id, next_id, back_id, 
                size
            ) 
            SELECT id, 
            father_id, 
            child_id, 
            next_id, 
            back_id, 
            size 
            FROM __temp__claro_subcontent
        ");
        $this->addSql("
            DROP TABLE __temp__claro_subcontent
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
                PRIMARY KEY(id)
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
            DROP INDEX IDX_60F90965EC942BCF
        ");
        $this->addSql("
            DROP INDEX unique_tool_name
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_tools AS 
            SELECT id, 
            plugin_id, 
            name, 
            display_name, 
            class, 
            is_workspace_required, 
            is_desktop_required, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop, 
            is_exportable, 
            has_options 
            FROM claro_tools
        ");
        $this->addSql("
            DROP TABLE claro_tools
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
            INSERT INTO claro_tools (
                id, plugin_id, name, display_name, 
                class, is_workspace_required, is_desktop_required, 
                is_displayable_in_workspace, is_displayable_in_desktop, 
                is_exportable, has_options
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            display_name, 
            class, 
            is_workspace_required, 
            is_desktop_required, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop, 
            is_exportable, 
            has_options 
            FROM __temp__claro_tools
        ");
        $this->addSql("
            DROP TABLE __temp__claro_tools
        ");
        $this->addSql("
            CREATE INDEX IDX_60F90965EC942BCF ON claro_tools (plugin_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_tool_name ON claro_tools (name)
        ");
        $this->addSql("
            DROP INDEX IDX_76CA6C4FEC942BCF
        ");
        $this->addSql("
            DROP INDEX tool
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_widget AS 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            icon, 
            is_exportable 
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
                icon VARCHAR(255) NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget (
                id, plugin_id, name, is_configurable, 
                icon, is_exportable
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            icon, 
            is_exportable 
            FROM __temp__claro_widget
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget
        ");
        $this->addSql("
            CREATE INDEX IDX_76CA6C4FEC942BCF ON claro_widget (plugin_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tool ON claro_widget (name)
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB382D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3FBE885E2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_widget_display AS 
            SELECT id, 
            parent_id, 
            workspace_id, 
            user_id, 
            widget_id, 
            is_locked, 
            is_visible, 
            is_desktop 
            FROM claro_widget_display
        ");
        $this->addSql("
            DROP TABLE claro_widget_display
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
            INSERT INTO claro_widget_display (
                id, parent_id, workspace_id, user_id, 
                widget_id, is_locked, is_visible, 
                is_desktop
            ) 
            SELECT id, 
            parent_id, 
            workspace_id, 
            user_id, 
            widget_id, 
            is_locked, 
            is_visible, 
            is_desktop 
            FROM __temp__claro_widget_display
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget_display
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
    }
}