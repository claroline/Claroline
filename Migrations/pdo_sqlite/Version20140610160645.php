<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/10 04:06:46
 */
class Version20140610160645 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_field_facet_value (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                stringValue VARCHAR(255) NOT NULL, 
                floatValue DOUBLE PRECISION NOT NULL, 
                dateValue DATETIME NOT NULL, 
                fieldFacet_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_35307C0AA76ED395 ON claro_field_facet_value (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_35307C0A9F9239AF ON claro_field_facet_value (fieldFacet_id)
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
            CREATE UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 ON claro_facet (name)
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INTEGER NOT NULL, 
                facet_id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                type INTEGER NOT NULL, 
                position INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F6C21DB25E237E06 ON claro_field_facet (name)
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2FC889F24 ON claro_field_facet (facet_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_field_facet_value
        ");
        $this->addSql("
            DROP TABLE claro_facet
        ");
        $this->addSql("
            DROP TABLE claro_field_facet
        ");
    }
}