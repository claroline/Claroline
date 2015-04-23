<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/08 05:40:04
 */
class Version20150408174003 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD COLUMN is_notifiable BOOLEAN DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
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
    }
}