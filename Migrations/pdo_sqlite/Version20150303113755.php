<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/03 11:37:56
 */
class Version20150303113755 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_ability AS 
            SELECT id, 
            name, 
            minActivityCount 
            FROM hevinci_ability
        ");
        $this->addSql("
            DROP TABLE hevinci_ability
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                minActivityCount INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_ability (id, name, minActivityCount) 
            SELECT id, 
            name, 
            minActivityCount 
            FROM __temp__hevinci_ability
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_ability
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_11E77B9D5E237E06 ON hevinci_ability (name)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_11E77B9D5E237E06
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_ability AS 
            SELECT id, 
            name, 
            minActivityCount 
            FROM hevinci_ability
        ");
        $this->addSql("
            DROP TABLE hevinci_ability
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                minActivityCount INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_ability (id, name, minActivityCount) 
            SELECT id, 
            name, 
            minActivityCount 
            FROM __temp__hevinci_ability
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_ability
        ");
    }
}