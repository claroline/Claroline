<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/11 02:40:12
 */
class Version20150311144010 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_widget_display_config (
                id INT IDENTITY NOT NULL, 
                workspace_id INT, 
                user_id INT, 
                widget_instance_id INT NOT NULL, 
                row_position INT NOT NULL, 
                column_position INT NOT NULL, 
                widget_width INT NOT NULL, 
                widget_height INT NOT NULL, 
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
            ADD CONSTRAINT DF_EBBE4972_D75C1C39 DEFAULT 4 FOR widget_width
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display_config 
            ADD CONSTRAINT DF_EBBE4972_1C21607 DEFAULT 3 FOR widget_height
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
            DROP TABLE claro_widget_display_config
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