<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/15 05:58:12
 */
class Version20130915175811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_4AE48D62A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log_desktop_widget_config AS 
            SELECT id, 
            user_id, 
            is_default, 
            amount 
            FROM claro_log_desktop_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_desktop_widget_config
        ");
        $this->addSql("
            CREATE TABLE claro_log_desktop_widget_config (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                amount INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_4AE48D62A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log_desktop_widget_config (id, user_id, is_default, amount) 
            SELECT id, 
            user_id, 
            is_default, 
            amount 
            FROM __temp__claro_log_desktop_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log_desktop_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_4AE48D62A76ED395 ON claro_log_desktop_widget_config (user_id)
        ");
        $this->addSql("
            DROP INDEX IDX_BC83196EA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log_hidden_workspace_widget_config AS 
            SELECT workspace_id, 
            user_id 
            FROM claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE claro_log_hidden_workspace_widget_config (
                workspace_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(workspace_id, user_id), 
                CONSTRAINT FK_BC83196EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log_hidden_workspace_widget_config (workspace_id, user_id) 
            SELECT workspace_id, 
            user_id 
            FROM __temp__claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_BC83196EA76ED395 ON claro_log_hidden_workspace_widget_config (user_id)
        ");
        $this->addSql("
            DROP INDEX IDX_BAB9695A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__simple_text_dekstop_widget_config AS 
            SELECT id, 
            user_id, 
            is_default, 
            content 
            FROM simple_text_dekstop_widget_config
        ");
        $this->addSql("
            DROP TABLE simple_text_dekstop_widget_config
        ");
        $this->addSql("
            CREATE TABLE simple_text_dekstop_widget_config (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_BAB9695A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO simple_text_dekstop_widget_config (id, user_id, is_default, content) 
            SELECT id, 
            user_id, 
            is_default, 
            content 
            FROM __temp__simple_text_dekstop_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__simple_text_dekstop_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_BAB9695A76ED395 ON simple_text_dekstop_widget_config (user_id)
        ");
        $this->addSql("
            DROP INDEX IDX_11925ED382D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__simple_text_workspace_widget_config AS 
            SELECT id, 
            workspace_id, 
            is_default, 
            content 
            FROM simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE simple_text_workspace_widget_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO simple_text_workspace_widget_config (
                id, workspace_id, is_default, content
            ) 
            SELECT id, 
            workspace_id, 
            is_default, 
            content 
            FROM __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED382D40A1F ON simple_text_workspace_widget_config (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_4AE48D62A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log_desktop_widget_config AS 
            SELECT id, 
            user_id, 
            is_default, 
            amount 
            FROM claro_log_desktop_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_desktop_widget_config
        ");
        $this->addSql("
            CREATE TABLE claro_log_desktop_widget_config (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                amount INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_4AE48D62A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log_desktop_widget_config (id, user_id, is_default, amount) 
            SELECT id, 
            user_id, 
            is_default, 
            amount 
            FROM __temp__claro_log_desktop_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log_desktop_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_4AE48D62A76ED395 ON claro_log_desktop_widget_config (user_id)
        ");
        $this->addSql("
            DROP INDEX IDX_BC83196EA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log_hidden_workspace_widget_config AS 
            SELECT workspace_id, 
            user_id 
            FROM claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE claro_log_hidden_workspace_widget_config (
                workspace_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(workspace_id, user_id), 
                CONSTRAINT FK_BC83196EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log_hidden_workspace_widget_config (workspace_id, user_id) 
            SELECT workspace_id, 
            user_id 
            FROM __temp__claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_BC83196EA76ED395 ON claro_log_hidden_workspace_widget_config (user_id)
        ");
        $this->addSql("
            DROP INDEX IDX_BAB9695A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__simple_text_dekstop_widget_config AS 
            SELECT id, 
            user_id, 
            is_default, 
            content 
            FROM simple_text_dekstop_widget_config
        ");
        $this->addSql("
            DROP TABLE simple_text_dekstop_widget_config
        ");
        $this->addSql("
            CREATE TABLE simple_text_dekstop_widget_config (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_BAB9695A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO simple_text_dekstop_widget_config (id, user_id, is_default, content) 
            SELECT id, 
            user_id, 
            is_default, 
            content 
            FROM __temp__simple_text_dekstop_widget_config
        ");
        $this->addSql("
            DROP TABLE __temp__simple_text_dekstop_widget_config
        ");
        $this->addSql("
            CREATE INDEX IDX_BAB9695A76ED395 ON simple_text_dekstop_widget_config (user_id)
        ");
        $this->addSql("
            DROP INDEX IDX_11925ED382D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__simple_text_workspace_widget_config AS 
            SELECT id, 
            workspace_id, 
            is_default, 
            content 
            FROM simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE simple_text_workspace_widget_config
        ");
        $this->addSql("
            CREATE TABLE simple_text_workspace_widget_config (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                is_default BOOLEAN NOT NULL, 
                content CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO simple_text_workspace_widget_config (
                id, workspace_id, is_default, content
            ) 
            SELECT id, 
            workspace_id, 
            is_default, 
            content 
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