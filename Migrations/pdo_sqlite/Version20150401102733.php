<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/01 10:27:35
 */
class Version20150401102733 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_EDBF854473484933
        ");
        $this->addSql("
            DROP INDEX IDX_EDBF8544FB9F58C
        ");
        $this->addSql("
            DROP INDEX IDX_EDBF85445FB14BA7
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_objective_competency AS 
            SELECT id, 
            level_id, 
            objective_id, 
            competency_id 
            FROM hevinci_objective_competency
        ");
        $this->addSql("
            DROP TABLE hevinci_objective_competency
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_competency (
                id INTEGER NOT NULL, 
                level_id INTEGER NOT NULL, 
                objective_id INTEGER NOT NULL, 
                competency_id INTEGER NOT NULL, 
                framework_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EDBF85445FB14BA7 FOREIGN KEY (level_id) 
                REFERENCES hevinci_level (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EDBF854473484933 FOREIGN KEY (objective_id) 
                REFERENCES hevinci_learning_objective (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EDBF8544FB9F58C FOREIGN KEY (competency_id) 
                REFERENCES hevinci_competency (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EDBF854437AECF72 FOREIGN KEY (framework_id) 
                REFERENCES hevinci_competency (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_objective_competency (
                id, level_id, objective_id, competency_id
            ) 
            SELECT id, 
            level_id, 
            objective_id, 
            competency_id 
            FROM __temp__hevinci_objective_competency
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_objective_competency
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF854473484933 ON hevinci_objective_competency (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF8544FB9F58C ON hevinci_objective_competency (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF85445FB14BA7 ON hevinci_objective_competency (level_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF854437AECF72 ON hevinci_objective_competency (framework_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_EDBF854473484933
        ");
        $this->addSql("
            DROP INDEX IDX_EDBF8544FB9F58C
        ");
        $this->addSql("
            DROP INDEX IDX_EDBF85445FB14BA7
        ");
        $this->addSql("
            DROP INDEX IDX_EDBF854437AECF72
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_objective_competency AS 
            SELECT id, 
            objective_id, 
            competency_id, 
            level_id 
            FROM hevinci_objective_competency
        ");
        $this->addSql("
            DROP TABLE hevinci_objective_competency
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_competency (
                id INTEGER NOT NULL, 
                objective_id INTEGER NOT NULL, 
                competency_id INTEGER NOT NULL, 
                level_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EDBF854473484933 FOREIGN KEY (objective_id) 
                REFERENCES hevinci_learning_objective (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EDBF8544FB9F58C FOREIGN KEY (competency_id) 
                REFERENCES hevinci_competency (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EDBF85445FB14BA7 FOREIGN KEY (level_id) 
                REFERENCES hevinci_level (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_objective_competency (
                id, objective_id, competency_id, level_id
            ) 
            SELECT id, 
            objective_id, 
            competency_id, 
            level_id 
            FROM __temp__hevinci_objective_competency
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_objective_competency
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF854473484933 ON hevinci_objective_competency (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF8544FB9F58C ON hevinci_objective_competency (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF85445FB14BA7 ON hevinci_objective_competency (level_id)
        ");
    }
}