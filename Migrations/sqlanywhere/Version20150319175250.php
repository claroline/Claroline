<?php

namespace Claroline\CoreBundle\Migrations\sqlanywhere;

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
                user_id INT DEFAULT NULL, 
                desktop_background_color VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B2066972A76ED395 ON claro_user_options (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_widget_display_config (
                id INT IDENTITY NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                widget_instance_id INT NOT NULL, 
                row_position INT NOT NULL, 
                column_position INT NOT NULL, 
                width INT DEFAULT 4 NOT NULL, 
                height INT DEFAULT 3 NOT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
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
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_display_config_unique_workspace ON claro_widget_display_config (
                widget_instance_id, workspace_id
            )
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
            ADD options_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD CONSTRAINT FK_EB8D28523ADB05F1 FOREIGN KEY (options_id) 
            REFERENCES claro_user_options (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28523ADB05F1 ON claro_user (options_id)
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
            ADD default_width INT DEFAULT 4 NOT NULL, 
            ADD default_height INT DEFAULT 3 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP FOREIGN KEY FK_EB8D28523ADB05F1
        ");
        $this->addSql("
            DROP TABLE claro_user_options
        ");
        $this->addSql("
            DROP TABLE claro_widget_display_config
        ");
        $this->addSql("
            DROP INDEX claro_ordered_tool.ordered_tool_unique_tool_user_type
        ");
        $this->addSql("
            DROP INDEX claro_ordered_tool.ordered_tool_unique_tool_ws_type
        ");
        $this->addSql("
            DROP INDEX claro_user.UNIQ_EB8D28523ADB05F1
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP options_id
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP default_width, 
            DROP default_height
        ");
    }
}