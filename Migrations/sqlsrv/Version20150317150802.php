<?php

namespace HeVinci\CompetencyBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/19 02:53:56
 */
class Version20150317150802 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_competency (
                id INT IDENTITY NOT NULL, 
                scale_id INT, 
                parent_id INT, 
                name NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX), 
                activityCount INT NOT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT, 
                PRIMARY KEY (id)
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
                competency_id INT NOT NULL, 
                activity_id INT NOT NULL, 
                PRIMARY KEY (competency_id, activity_id)
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
                id INT IDENTITY NOT NULL, 
                competency_id INT NOT NULL, 
                ability_id INT NOT NULL, 
                level_id INT NOT NULL, 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D3477F405E237E06 ON hevinci_scale (name) 
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE hevinci_level (
                id INT IDENTITY NOT NULL, 
                scale_id INT NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                value INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A5EB96D7F73142C2 ON hevinci_level (scale_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability (
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                minActivityCount INT NOT NULL, 
                activityCount INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_11E77B9D5E237E06 ON hevinci_ability (name) 
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability_activity (
                ability_id INT NOT NULL, 
                activity_id INT NOT NULL, 
                PRIMARY KEY (ability_id, activity_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_46D92D328016D8B2 ON hevinci_ability_activity (ability_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_46D92D3281C06096 ON hevinci_ability_activity (activity_id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD CONSTRAINT FK_61ECD5E6F73142C2 FOREIGN KEY (scale_id) 
            REFERENCES hevinci_scale (id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD CONSTRAINT FK_61ECD5E6727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_activity 
            ADD CONSTRAINT FK_82CDDCBFFB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_activity 
            ADD CONSTRAINT FK_82CDDCBF81C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            ADD CONSTRAINT FK_38178A41FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            ADD CONSTRAINT FK_38178A418016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            ADD CONSTRAINT FK_38178A415FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_level 
            ADD CONSTRAINT FK_A5EB96D7F73142C2 FOREIGN KEY (scale_id) 
            REFERENCES hevinci_scale (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_activity 
            ADD CONSTRAINT FK_46D92D328016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_activity 
            ADD CONSTRAINT FK_46D92D3281C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP CONSTRAINT FK_61ECD5E6727ACA70
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_activity 
            DROP CONSTRAINT FK_82CDDCBFFB9F58C
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            DROP CONSTRAINT FK_38178A41FB9F58C
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP CONSTRAINT FK_61ECD5E6F73142C2
        ");
        $this->addSql("
            ALTER TABLE hevinci_level 
            DROP CONSTRAINT FK_A5EB96D7F73142C2
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            DROP CONSTRAINT FK_38178A415FB14BA7
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            DROP CONSTRAINT FK_38178A418016D8B2
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_activity 
            DROP CONSTRAINT FK_46D92D328016D8B2
        ");
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