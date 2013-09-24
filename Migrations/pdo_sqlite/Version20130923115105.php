<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 11:51:05
 */
class Version20130923115105 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_11925ED382D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__simple_text_workspace_widget_config AS 
            SELECT id, 
            workspace_id, 
            content 
            FROM simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE simple_text_workspace_widget_config (
                id INTEGER NOT NULL, 
                content CLOB NOT NULL, 
                displayConfig_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_11925ED3EF00646E FOREIGN KEY (displayConfig_id) 
                REFERENCES claro_widget_display (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO simple_text_workspace_widget_config (id, displayConfig_id, content) 
            SELECT id, 
            workspace_id, 
            content 
            FROM __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED3EF00646E ON simple_text_workspace_widget_config (displayConfig_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_11925ED3EF00646E
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__simple_text_workspace_widget_config AS 
            SELECT id, 
            content, 
            displayConfig_id 
            FROM simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE simple_text_workspace_widget_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                content CLOB NOT NULL, 
                is_default BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO simple_text_workspace_widget_config (id, content, workspace_id) 
            SELECT id, 
            content, 
            displayConfig_id 
            FROM __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED382D40A1F ON simple_text_workspace_widget_config (workspace_id)
        ");
    }
}