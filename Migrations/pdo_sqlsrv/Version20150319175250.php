<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/19 05:52:53
 */
class Version20150319175250 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_options (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                desktop_background_color NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B2066972A76ED395 ON claro_user_options (user_id) 
            WHERE user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_widget_display_config (
                id INT IDENTITY NOT NULL, 
                workspace_id INT, 
                user_id INT, 
                widget_instance_id INT NOT NULL, 
                row_position INT NOT NULL, 
                column_position INT NOT NULL, 
                width INT NOT NULL, 
                height INT NOT NULL, 
                color NVARCHAR(255), 
                PRIMARY KEY (id)
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
            WHERE widget_instance_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_display_config_unique_workspace ON claro_widget_display_config (
                widget_instance_id, workspace_id
            ) 
            WHERE widget_instance_id IS NOT NULL 
            AND workspace_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT DF_EBBE4972_8C1A452F DEFAULT 4 FOR width
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT DF_EBBE4972_F54DE50F DEFAULT 3 FOR height
        ");
        $this->addSql("
            ALTER TABLE claro_user_options 
            ADD CONSTRAINT FK_B2066972A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT FK_EBBE497282D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT FK_EBBE4972A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT FK_EBBE497244BF891 FOREIGN KEY (widget_instance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD options_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D28523ADB05F1 FOREIGN KEY (options_id) 
            REFERENCES claro_user_options (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28523ADB05F1 ON claro_user (options_id) 
            WHERE options_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool (
                tool_id, user_id, ordered_tool_type
            ) 
            WHERE tool_id IS NOT NULL 
            AND user_id IS NOT NULL 
            AND ordered_tool_type IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool (
                tool_id, workspace_id, ordered_tool_type
            ) 
            WHERE tool_id IS NOT NULL 
            AND workspace_id IS NOT NULL 
            AND ordered_tool_type IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD default_width INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD CONSTRAINT DF_76CA6C4F_653C1121 DEFAULT 4 FOR default_width
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD default_height INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD CONSTRAINT DF_76CA6C4F_121CEE5C DEFAULT 3 FOR default_height
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP CONSTRAINT FK_EB8D28523ADB05F1
        ");
        $this->addSql("
            DROP TABLE claro_user_options
        ");
        $this->addSql("
            DROP TABLE claro_widget_display_config
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'ordered_tool_unique_tool_user_type'
            ) 
            ALTER TABLE claro_ordered_tool 
            DROP CONSTRAINT ordered_tool_unique_tool_user_type ELSE 
            DROP INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'ordered_tool_unique_tool_ws_type'
            ) 
            ALTER TABLE claro_ordered_tool 
            DROP CONSTRAINT ordered_tool_unique_tool_ws_type ELSE 
            DROP INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_EB8D28523ADB05F1'
            ) 
            ALTER TABLE claro_user 
            DROP CONSTRAINT UNIQ_EB8D28523ADB05F1 ELSE 
            DROP INDEX UNIQ_EB8D28523ADB05F1 ON claro_user
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN options_id
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP COLUMN default_width
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP COLUMN default_height
        ");
    }
}