<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/15 10:41:58
 */
class Version20150415104156 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_home_tab 
            ADD COLUMN icon VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            ADD COLUMN icon VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_A9744CCEA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_A9744CCE82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_home_tab AS 
            SELECT id, 
            user_id, 
            workspace_id, 
            name, 
            type 
            FROM claro_home_tab
        ");
        $this->addSql("
            DROP TABLE claro_home_tab
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A9744CCEA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A9744CCE82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_home_tab (
                id, user_id, workspace_id, name, type
            ) 
            SELECT id, 
            user_id, 
            workspace_id, 
            name, 
            type 
            FROM __temp__claro_home_tab
        ");
        $this->addSql("
            DROP TABLE __temp__claro_home_tab
        ");
        $this->addSql("
            CREATE INDEX IDX_A9744CCEA76ED395 ON claro_home_tab (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A9744CCE82D40A1F ON claro_home_tab (workspace_id)
        ");
        $this->addSql("
            DROP INDEX IDX_5F89A38582D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_5F89A385A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_5F89A385FBE885E2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_widget_instance AS 
            SELECT id, 
            workspace_id, 
            user_id, 
            widget_id, 
            is_admin, 
            is_desktop, 
            name 
            FROM claro_widget_instance
        ");
        $this->addSql("
            DROP TABLE claro_widget_instance
        ");
        $this->addSql("
            CREATE TABLE claro_widget_instance (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                widget_id INTEGER NOT NULL, 
                is_admin BOOLEAN NOT NULL, 
                is_desktop BOOLEAN NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5F89A38582D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_5F89A385A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_5F89A385FBE885E2 FOREIGN KEY (widget_id) 
                REFERENCES claro_widget (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget_instance (
                id, workspace_id, user_id, widget_id, 
                is_admin, is_desktop, name
            ) 
            SELECT id, 
            workspace_id, 
            user_id, 
            widget_id, 
            is_admin, 
            is_desktop, 
            name 
            FROM __temp__claro_widget_instance
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget_instance
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A38582D40A1F ON claro_widget_instance (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A385A76ED395 ON claro_widget_instance (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A385FBE885E2 ON claro_widget_instance (widget_id)
        ");
    }
}