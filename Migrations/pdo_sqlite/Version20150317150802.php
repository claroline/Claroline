<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/19 02:53:55
 */
class Version20150317150802 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_competency (
                id INTEGER NOT NULL, 
                scale_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                activityCount INTEGER NOT NULL, 
                lft INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                root INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_61ECD5E6F73142C2 ON hevinci_competency (scale_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_61ECD5E6727ACA70 ON hevinci_competency (parent_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_competency_activity (
                competency_id INTEGER NOT NULL, 
                activity_id INTEGER NOT NULL, 
                PRIMARY KEY(competency_id, activity_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_82CDDCBFFB9F58C ON hevinci_competency_activity (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_82CDDCBF81C06096 ON hevinci_competency_activity (activity_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_competency_ability (
                id INTEGER NOT NULL, 
                competency_id INTEGER NOT NULL, 
                ability_id INTEGER NOT NULL, 
                level_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_38178A41FB9F58C ON hevinci_competency_ability (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_38178A418016D8B2 ON hevinci_competency_ability (ability_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_38178A415FB14BA7 ON hevinci_competency_ability (level_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_scale (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D3477F405E237E06 ON hevinci_scale (name)
        ");
        $this->addSql("
            CREATE TABLE hevinci_level (
                id INTEGER NOT NULL, 
                scale_id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                value INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A5EB96D7F73142C2 ON hevinci_level (scale_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                minActivityCount INTEGER NOT NULL, 
                activityCount INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_11E77B9D5E237E06 ON hevinci_ability (name)
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability_activity (
                ability_id INTEGER NOT NULL, 
                activity_id INTEGER NOT NULL, 
                PRIMARY KEY(ability_id, activity_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_46D92D328016D8B2 ON hevinci_ability_activity (ability_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_46D92D3281C06096 ON hevinci_ability_activity (activity_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_competency
        ");
        $this->addSql("
            DROP TABLE hevinci_competency_activity
        ");
        $this->addSql("
            DROP TABLE hevinci_competency_ability
        ");
        $this->addSql("
            DROP TABLE hevinci_scale
        ");
        $this->addSql("
            DROP TABLE hevinci_level
        ");
        $this->addSql("
            DROP TABLE hevinci_ability
        ");
        $this->addSql("
            DROP TABLE hevinci_ability_activity
        ");
    }
}