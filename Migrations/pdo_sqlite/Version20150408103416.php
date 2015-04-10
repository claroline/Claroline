<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/08 10:34:18
 */
class Version20150408103416 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_1D76301AEC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_theme AS 
            SELECT id, 
            plugin_id, 
            name, 
            path 
            FROM claro_theme
        ");
        $this->addSql("
            DROP TABLE claro_theme
        ");
        $this->addSql("
            CREATE TABLE claro_theme (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                path VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1D76301AEC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_theme (id, plugin_id, name, path) 
            SELECT id, 
            plugin_id, 
            name, 
            path 
            FROM __temp__claro_theme
        ");
        $this->addSql("
            DROP TABLE __temp__claro_theme
        ");
        $this->addSql("
            CREATE INDEX IDX_1D76301AEC942BCF ON claro_theme (plugin_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_1D76301AEC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_theme AS 
            SELECT id, 
            plugin_id, 
            name, 
            path 
            FROM claro_theme
        ");
        $this->addSql("
            DROP TABLE claro_theme
        ");
        $this->addSql("
            CREATE TABLE claro_theme (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                path VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1D76301AEC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_theme (id, plugin_id, name, path) 
            SELECT id, 
            plugin_id, 
            name, 
            path 
            FROM __temp__claro_theme
        ");
        $this->addSql("
            DROP TABLE __temp__claro_theme
        ");
        $this->addSql("
            CREATE INDEX IDX_1D76301AEC942BCF ON claro_theme (plugin_id)
        ");
    }
}