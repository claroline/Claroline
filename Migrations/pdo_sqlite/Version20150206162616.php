<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/06 04:26:17
 */
class Version20150206162616 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_A5EB96D7F73142C2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_level AS 
            SELECT id, 
            scale_id, 
            name, 
            value 
            FROM hevinci_level
        ");
        $this->addSql("
            DROP TABLE hevinci_level
        ");
        $this->addSql("
            CREATE TABLE hevinci_level (
                id INTEGER NOT NULL, 
                scale_id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                value INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A5EB96D7F73142C2 FOREIGN KEY (scale_id) 
                REFERENCES hevinci_scale (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_level (id, scale_id, name, value) 
            SELECT id, 
            scale_id, 
            name, 
            value 
            FROM __temp__hevinci_level
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_level
        ");
        $this->addSql("
            CREATE INDEX IDX_A5EB96D7F73142C2 ON hevinci_level (scale_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_A5EB96D7F73142C2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_level AS 
            SELECT id, 
            scale_id, 
            name, 
            value 
            FROM hevinci_level
        ");
        $this->addSql("
            DROP TABLE hevinci_level
        ");
        $this->addSql("
            CREATE TABLE hevinci_level (
                id INTEGER NOT NULL, 
                scale_id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                value INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A5EB96D7F73142C2 FOREIGN KEY (scale_id) 
                REFERENCES hevinci_scale (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_level (id, scale_id, name, value) 
            SELECT id, 
            scale_id, 
            name, 
            value 
            FROM __temp__hevinci_level
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_level
        ");
        $this->addSql("
            CREATE INDEX IDX_A5EB96D7F73142C2 ON hevinci_level (scale_id)
        ");
    }
}