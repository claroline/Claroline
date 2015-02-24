<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/24 04:31:16
 */
class Version20150224163115 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_ability 
            ADD COLUMN minActivityCount INTEGER NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_ability AS 
            SELECT id, 
            name 
            FROM hevinci_ability
        ");
        $this->addSql("
            DROP TABLE hevinci_ability
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_ability (id, name) 
            SELECT id, 
            name 
            FROM __temp__hevinci_ability
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_ability
        ");
    }
}