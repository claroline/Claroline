<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 09:29:17
 */
class Version20130923092917 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD COLUMN is_°desktop BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
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
            workspace_id, 
            user_id, 
            widget_id, 
            is_admin, 
            name 
            FROM claro_widget_display
        ");
        $this->addSql("
            DROP TABLE claro_widget_display
        ");
        $this->addSql("
            CREATE TABLE claro_widget_display (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                widget_id INTEGER NOT NULL, 
                is_admin BOOLEAN NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
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
                id, workspace_id, user_id, widget_id, 
                is_admin, name
            ) 
            SELECT id, 
            workspace_id, 
            user_id, 
            widget_id, 
            is_admin, 
            name 
            FROM __temp__claro_widget_display
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget_display
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