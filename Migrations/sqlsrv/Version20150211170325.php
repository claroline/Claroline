<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/11 05:03:27
 */
class Version20150211170325 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_panel_facet (
                id INT IDENTITY NOT NULL, 
                facet_id INT NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                position INT NOT NULL, 
                isDefaultCollapsed BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_DA3985FFC889F24 ON claro_panel_facet (facet_id)
        ");
        $this->addSql("
            ALTER TABLE claro_panel_facet 
            ADD CONSTRAINT FK_DA3985FFC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            DROP CONSTRAINT FK_F6C21DB2FC889F24
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_F6C21DB2FC889F24'
            ) 
            ALTER TABLE claro_field_facet 
            DROP CONSTRAINT IDX_F6C21DB2FC889F24 ELSE 
            DROP INDEX IDX_F6C21DB2FC889F24 ON claro_field_facet
        ");
        $this->addSql("
            sp_RENAME 'claro_field_facet.facet_id', 
            'panelFacet_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2E99038C0 FOREIGN KEY (panelFacet_id) 
            REFERENCES claro_panel_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2E99038C0 ON claro_field_facet (panelFacet_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_field_facet 
            DROP CONSTRAINT FK_F6C21DB2E99038C0
        ");
        $this->addSql("
            DROP TABLE claro_panel_facet
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_F6C21DB2E99038C0'
            ) 
            ALTER TABLE claro_field_facet 
            DROP CONSTRAINT IDX_F6C21DB2E99038C0 ELSE 
            DROP INDEX IDX_F6C21DB2E99038C0 ON claro_field_facet
        ");
        $this->addSql("
            sp_RENAME 'claro_field_facet.panelfacet_id', 
            'facet_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2FC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2FC889F24 ON claro_field_facet (facet_id)
        ");
    }
}