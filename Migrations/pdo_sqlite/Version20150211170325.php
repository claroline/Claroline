<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/11 05:03:26
 */
class Version20150211170325 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_panel_facet (
                id INTEGER NOT NULL, 
                facet_id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                position INTEGER NOT NULL, 
                isDefaultCollapsed BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_DA3985FFC889F24 ON claro_panel_facet (facet_id)
        ");
        $this->addSql("
            DROP INDEX IDX_F6C21DB2FC889F24
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_field_facet AS 
            SELECT id, 
            facet_id, 
            name, 
            type, 
            position, 
            isVisibleByOwner, 
            isEditableByOwner 
            FROM claro_field_facet
        ");
        $this->addSql("
            DROP TABLE claro_field_facet
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                type INTEGER NOT NULL, 
                position INTEGER NOT NULL, 
                isVisibleByOwner BOOLEAN NOT NULL, 
                isEditableByOwner BOOLEAN NOT NULL, 
                panelFacet_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F6C21DB2E99038C0 FOREIGN KEY (panelFacet_id) 
                REFERENCES claro_panel_facet (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_field_facet (
                id, panelFacet_id, name, type, position, 
                isVisibleByOwner, isEditableByOwner
            ) 
            SELECT id, 
            facet_id, 
            name, 
            type, 
            position, 
            isVisibleByOwner, 
            isEditableByOwner 
            FROM __temp__claro_field_facet
        ");
        $this->addSql("
            DROP TABLE __temp__claro_field_facet
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2E99038C0 ON claro_field_facet (panelFacet_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_panel_facet
        ");
        $this->addSql("
            DROP INDEX IDX_F6C21DB2E99038C0
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_field_facet AS 
            SELECT id, 
            name, 
            type, 
            position, 
            isVisibleByOwner, 
            isEditableByOwner, 
            panelFacet_id 
            FROM claro_field_facet
        ");
        $this->addSql("
            DROP TABLE claro_field_facet
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INTEGER NOT NULL, 
                facet_id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                type INTEGER NOT NULL, 
                position INTEGER NOT NULL, 
                isVisibleByOwner BOOLEAN NOT NULL, 
                isEditableByOwner BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F6C21DB2E99038C0 FOREIGN KEY (facet_id) 
                REFERENCES claro_panel_facet (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F6C21DB2FC889F24 FOREIGN KEY (facet_id) 
                REFERENCES claro_facet (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_field_facet (
                id, name, type, position, isVisibleByOwner, 
                isEditableByOwner, facet_id
            ) 
            SELECT id, 
            name, 
            type, 
            position, 
            isVisibleByOwner, 
            isEditableByOwner, 
            panelFacet_id 
            FROM __temp__claro_field_facet
        ");
        $this->addSql("
            DROP TABLE __temp__claro_field_facet
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2FC889F24 ON claro_field_facet (facet_id)
        ");
    }
}