<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/17 01:43:52
 */
class Version20130917134352 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD COLUMN name VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
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
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
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
    }
}