<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/17 05:57:30
 */
class Version20150317175729 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_facet 
            ADD COLUMN forceCreationForm BOOLEAN NOT NULL
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
            position, 
            isVisibleByOwner 
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
                isVisibleByOwner BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_facet (
                id, name, position, isVisibleByOwner
            ) 
            SELECT id, 
            name, 
            position, 
            isVisibleByOwner 
            FROM __temp__claro_facet
        ");
        $this->addSql("
            DROP TABLE __temp__claro_facet
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 ON claro_facet (name)
        ");
    }
}