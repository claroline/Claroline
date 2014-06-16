<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/16 09:50:29
 */
class Version20140616095028 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_facet_role (
                facet_id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                PRIMARY KEY(facet_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CDD5845DFC889F24 ON claro_facet_role (facet_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDD5845DD60322AC ON claro_facet_role (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet_role (
                id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                field_id INTEGER NOT NULL, 
                canOpen BOOLEAN NOT NULL, 
                canEdit BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_12F52A52D60322AC ON claro_field_facet_role (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_12F52A52443707B0 ON claro_field_facet_role (field_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_F6C21DB25E237E06
        ");
        $this->addSql("
            DROP INDEX IDX_F6C21DB2FC889F24
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_field_facet AS 
            SELECT id, 
            facet_id, 
            type, 
            position, 
            name 
            FROM claro_field_facet
        ");
        $this->addSql("
            DROP TABLE claro_field_facet
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INTEGER NOT NULL, 
                facet_id INTEGER NOT NULL, 
                type INTEGER NOT NULL, 
                position INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F6C21DB2FC889F24 FOREIGN KEY (facet_id) 
                REFERENCES claro_facet (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_field_facet (id, facet_id, type, position, name) 
            SELECT id, 
            facet_id, 
            type, 
            position, 
            name 
            FROM __temp__claro_field_facet
        ");
        $this->addSql("
            DROP TABLE __temp__claro_field_facet
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2FC889F24 ON claro_field_facet (facet_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_facet_role
        ");
        $this->addSql("
            DROP TABLE claro_field_facet_role
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
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F6C21DB25E237E06 ON claro_field_facet (name)
        ");
    }
}