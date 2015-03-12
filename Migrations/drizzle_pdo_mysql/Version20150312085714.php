<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/12 08:57:17
 */
class Version20150312085714 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
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
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            ADD default_width INT DEFAULT 4 NOT NULL, 
            ADD default_height INT DEFAULT 3 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_widget_display_config
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP default_width, 
            DROP default_height
        ");
    }
}