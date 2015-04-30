<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/30 11:34:43
 */
class Version20150430113442 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_objective_competency (
                id INTEGER NOT NULL, 
                objective_id INTEGER NOT NULL, 
                competency_id INTEGER NOT NULL, 
                level_id INTEGER NOT NULL, 
                framework_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
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
        $this->addSql("
            CREATE TABLE hevinci_ability_progress (
                id INTEGER NOT NULL, 
                ability_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                passed_activity_ids CLOB DEFAULT NULL, 
                passed_activity_count INTEGER NOT NULL, 
                status VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C8ACD62E8016D8B2 ON hevinci_ability_progress (ability_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C8ACD62EA76ED395 ON hevinci_ability_progress (user_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_competency_progress (
                id INTEGER NOT NULL, 
                competency_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                level_id INTEGER DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CB827A3FB9F58C ON hevinci_competency_progress (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CB827A3A76ED395 ON hevinci_competency_progress (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CB827A35FB14BA7 ON hevinci_competency_progress (level_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_learning_objective (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_10D9D6545E237E06 ON hevinci_learning_objective (name)
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_user (
                objective_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(objective_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6D032C1573484933 ON hevinci_objective_user (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6D032C15A76ED395 ON hevinci_objective_user (user_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_group (
                objective_id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                PRIMARY KEY(objective_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E073484933 ON hevinci_objective_group (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E0FE54D947 ON hevinci_objective_group (group_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_objective_competency
        ");
        $this->addSql("
            DROP TABLE hevinci_ability_progress
        ");
        $this->addSql("
            DROP TABLE hevinci_competency_progress
        ");
        $this->addSql("
            DROP TABLE hevinci_learning_objective
        ");
        $this->addSql("
            DROP TABLE hevinci_objective_user
        ");
        $this->addSql("
            DROP TABLE hevinci_objective_group
        ");
    }
}