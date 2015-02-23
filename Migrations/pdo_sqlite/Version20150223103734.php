<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/23 10:37:35
 */
class Version20150223103734 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD COLUMN type VARCHAR(50) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD COLUMN authors CLOB NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD COLUMN description CLOB DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD COLUMN license CLOB NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_bundle AS 
            SELECT id, 
            name, 
            version 
            FROM claro_bundle
        ");
        $this->addSql("
            DROP TABLE claro_bundle
        ");
        $this->addSql("
            CREATE TABLE claro_bundle (
                id INTEGER NOT NULL, 
                name VARCHAR(100) NOT NULL, 
                version VARCHAR(50) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_bundle (id, name, version) 
            SELECT id, 
            name, 
            version 
            FROM __temp__claro_bundle
        ");
        $this->addSql("
            DROP TABLE __temp__claro_bundle
        ");
    }
}