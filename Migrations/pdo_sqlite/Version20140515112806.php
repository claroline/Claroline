<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/15 11:28:07
 */
class Version20140515112806 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX tag_unique_name_and_user
        ");
        $this->addSql("
            DROP INDEX IDX_C8EFD7EFA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_workspace_tag AS 
            SELECT id, 
            user_id, 
            name 
            FROM claro_workspace_tag
        ");
        $this->addSql("
            DROP TABLE claro_workspace_tag
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_tag (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_C8EFD7EFA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_C8EFD7EF82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_workspace_tag (id, user_id, name) 
            SELECT id, 
            user_id, 
            name 
            FROM __temp__claro_workspace_tag
        ");
        $this->addSql("
            DROP TABLE __temp__claro_workspace_tag
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tag_unique_name_and_user ON claro_workspace_tag (user_id, name)
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EFA76ED395 ON claro_workspace_tag (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_C8EFD7EFA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            DROP INDEX tag_unique_name_and_user
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_workspace_tag AS 
            SELECT id, 
            user_id, 
            name 
            FROM claro_workspace_tag
        ");
        $this->addSql("
            DROP TABLE claro_workspace_tag
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_tag (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_C8EFD7EFA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_workspace_tag (id, user_id, name) 
            SELECT id, 
            user_id, 
            name 
            FROM __temp__claro_workspace_tag
        ");
        $this->addSql("
            DROP TABLE __temp__claro_workspace_tag
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EFA76ED395 ON claro_workspace_tag (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tag_unique_name_and_user ON claro_workspace_tag (user_id, name)
        ");
    }
}