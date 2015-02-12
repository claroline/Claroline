<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/12 11:36:14
 */
class Version20150212113613 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_scale AS 
            SELECT id, 
            name 
            FROM hevinci_scale
        ");
        $this->addSql("
            DROP TABLE hevinci_scale
        ");
        $this->addSql("
            CREATE TABLE hevinci_scale (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_scale (id, name) 
            SELECT id, 
            name 
            FROM __temp__hevinci_scale
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_scale
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D3477F405E237E06 ON hevinci_scale (name)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_D3477F405E237E06
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_scale AS 
            SELECT id, 
            name 
            FROM hevinci_scale
        ");
        $this->addSql("
            DROP TABLE hevinci_scale
        ");
        $this->addSql("
            CREATE TABLE hevinci_scale (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_scale (id, name) 
            SELECT id, 
            name 
            FROM __temp__hevinci_scale
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_scale
        ");
    }
}