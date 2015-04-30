<?php

namespace HeVinci\CompetencyBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                objective_id INT NOT NULL, 
                competency_id INT NOT NULL, 
                level_id INT NOT NULL, 
                framework_id INT NOT NULL, 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                ability_id INT, 
                user_id INT, 
                passed_activity_ids VARCHAR(MAX), 
                passed_activity_count INT NOT NULL, 
                status NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                competency_id INT, 
                user_id INT, 
                level_id INT, 
                type NVARCHAR(255) NOT NULL, 
                date DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
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
                id INT IDENTITY NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_10D9D6545E237E06 ON hevinci_learning_objective (name) 
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_user (
                objective_id INT NOT NULL, 
                user_id INT NOT NULL, 
                PRIMARY KEY (objective_id, user_id)
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
                objective_id INT NOT NULL, 
                group_id INT NOT NULL, 
                PRIMARY KEY (objective_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E073484933 ON hevinci_objective_group (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E0FE54D947 ON hevinci_objective_group (group_id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF854473484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF8544FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF85445FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF854437AECF72 FOREIGN KEY (framework_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_progress 
            ADD CONSTRAINT FK_C8ACD62E8016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_progress 
            ADD CONSTRAINT FK_C8ACD62EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A3FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A35FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_user 
            ADD CONSTRAINT FK_6D032C1573484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_user 
            ADD CONSTRAINT FK_6D032C15A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_group 
            ADD CONSTRAINT FK_FFDC9E073484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_group 
            ADD CONSTRAINT FK_FFDC9E0FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            DROP CONSTRAINT FK_EDBF854473484933
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_user 
            DROP CONSTRAINT FK_6D032C1573484933
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_group 
            DROP CONSTRAINT FK_FFDC9E073484933
        ");
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