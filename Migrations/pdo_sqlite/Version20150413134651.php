<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/13 01:46:54
 */
class Version20150413134651 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_AEC626935E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_AEC62693EC942BCF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_type AS 
            SELECT id, 
            plugin_id, 
            name, 
            is_exportable, 
            defaultMask 
            FROM claro_resource_type
        ");
        $this->addSql("
            DROP TABLE claro_resource_type
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type (
                id INTEGER NOT NULL, 
                plugin_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                is_exportable BOOLEAN NOT NULL, 
                defaultMask INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_AEC62693EC942BCF FOREIGN KEY (plugin_id) 
                REFERENCES claro_plugin (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_type (
                id, plugin_id, name, is_exportable, 
                defaultMask
            ) 
            SELECT id, 
            plugin_id, 
            name, 
            is_exportable, 
            defaultMask 
            FROM __temp__claro_resource_type
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_type
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_AEC626935E237E06 ON claro_resource_type (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693EC942BCF ON claro_resource_type (plugin_id)
        ");
        $this->addSql("
            DROP INDEX IDX_1D76301AEC942BCF
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD COLUMN is_notifiable BOOLEAN DEFAULT '0' NOT NULL
        ");
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
    }
}