<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/24 01:32:23
 */
class Version20150224133221 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX plugin_unique_name
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_plugin AS 
            SELECT id, 
            vendor_name, 
            short_name, 
            has_options 
            FROM claro_plugin
        ");
        $this->addSql("
            DROP TABLE claro_plugin
        ");
        $this->addSql("
            CREATE TABLE claro_plugin (
                id INTEGER NOT NULL, 
                vendor_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, 
                short_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, 
                has_options BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_plugin (
                id, vendor_name, short_name, has_options
            ) 
            SELECT id, 
            vendor_name, 
            short_name, 
            has_options 
            FROM __temp__claro_plugin
        ");
        $this->addSql("
            DROP TABLE __temp__claro_plugin
        ");
        $this->addSql("
            CREATE UNIQUE INDEX plugin_unique_name ON claro_plugin (vendor_name, short_name)
        ");
        $this->addSql("
            DROP INDEX UNIQ_76CA6C4F5E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_76CA6C4FEC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_widget AS 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            is_exportable, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop 
            FROM claro_widget
        ");
        $this->addSql("
            DROP TABLE claro_widget
        ");
        $this->addSql("
            CREATE TABLE claro_widget (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                is_configurable BOOLEAN NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                is_displayable_in_workspace BOOLEAN NOT NULL, 
                is_displayable_in_desktop BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_76CA6C4FEC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget (
                id, plugin_id, name, is_configurable, 
                is_exportable, is_displayable_in_workspace, 
                is_displayable_in_desktop
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            is_configurable, 
            is_exportable, 
            is_displayable_in_workspace, 
            is_displayable_in_desktop 
            FROM __temp__claro_widget
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_76CA6C4F5E237E06 ON claro_widget (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_76CA6C4FEC942BCF ON claro_widget (plugin_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_plugin 
            ADD COLUMN icon VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD COLUMN icon VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
    }
}