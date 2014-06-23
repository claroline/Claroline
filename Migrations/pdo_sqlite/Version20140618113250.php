<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/18 11:32:52
 */
class Version20140618113250 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_facet 
            ADD COLUMN isVisibleByOwner BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD COLUMN isVisibleByOwner BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD COLUMN isEditableByOwner BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_DCBA6D3A5E237E06
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_facet AS 
            SELECT id, 
            name, 
            position 
            FROM claro_facet
        ");
        $this->addSql("
            DROP TABLE claro_facet
        ");
        $this->addSql("
            CREATE TABLE claro_facet (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                position INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_facet (id, name, position) 
            SELECT id, 
            name, 
            position 
            FROM __temp__claro_facet
        ");
        $this->addSql("
            DROP TABLE __temp__claro_facet
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 ON claro_facet (name)
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
            position 
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_F6C21DB2FC889F24 FOREIGN KEY (facet_id) 
                REFERENCES claro_facet (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_field_facet (id, facet_id, name, type, position) 
            SELECT id, 
            facet_id, 
            name, 
            type, 
            position 
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