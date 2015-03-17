<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/17 03:08:04
 */
class Version20150317150802 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD COLUMN activityCount INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability 
            ADD COLUMN activityCount INTEGER NOT NULL
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
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_11E77B9D5E237E06 ON hevinci_ability (name)
        ");
        $this->addSql("
            DROP INDEX IDX_61ECD5E6F73142C2
        ");
        $this->addSql("
            DROP INDEX IDX_61ECD5E6727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_competency AS 
            SELECT id, 
            scale_id, 
            parent_id, 
            name, 
            description, 
            lft, 
            lvl, 
            rgt, 
            root 
            FROM hevinci_competency
        ");
        $this->addSql("
            DROP TABLE hevinci_competency
        ");
        $this->addSql("
            CREATE TABLE hevinci_competency (
                id INTEGER NOT NULL, 
                scale_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                lft INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                root INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_61ECD5E6F73142C2 FOREIGN KEY (scale_id) 
                REFERENCES hevinci_scale (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_61ECD5E6727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES hevinci_competency (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_competency (
                id, scale_id, parent_id, name, description, 
                lft, lvl, rgt, root
            ) 
            SELECT id, 
            scale_id, 
            parent_id, 
            name, 
            description, 
            lft, 
            lvl, 
            rgt, 
            root 
            FROM __temp__hevinci_competency
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_competency
        ");
        $this->addSql("
            CREATE INDEX IDX_61ECD5E6F73142C2 ON hevinci_competency (scale_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_61ECD5E6727ACA70 ON hevinci_competency (parent_id)
        ");
    }
}