<?php

namespace HeVinci\CompetencyBundle\Migrations\mysqli;

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
                id INT AUTO_INCREMENT NOT NULL, 
                objective_id INT NOT NULL, 
                competency_id INT NOT NULL, 
                level_id INT NOT NULL, 
                framework_id INT NOT NULL, 
                INDEX IDX_EDBF854473484933 (objective_id), 
                INDEX IDX_EDBF8544FB9F58C (competency_id), 
                INDEX IDX_EDBF85445FB14BA7 (level_id), 
                INDEX IDX_EDBF854437AECF72 (framework_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability_progress (
                id INT AUTO_INCREMENT NOT NULL, 
                ability_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                passed_activity_ids LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', 
                passed_activity_count INT NOT NULL, 
                status VARCHAR(255) NOT NULL, 
                INDEX IDX_C8ACD62E8016D8B2 (ability_id), 
                INDEX IDX_C8ACD62EA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_competency_progress (
                id INT AUTO_INCREMENT NOT NULL, 
                competency_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                level_id INT DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                date DATETIME NOT NULL, 
                INDEX IDX_CB827A3FB9F58C (competency_id), 
                INDEX IDX_CB827A3A76ED395 (user_id), 
                INDEX IDX_CB827A35FB14BA7 (level_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_learning_objective (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_10D9D6545E237E06 (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_user (
                objective_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_6D032C1573484933 (objective_id), 
                INDEX IDX_6D032C15A76ED395 (user_id), 
                PRIMARY KEY(objective_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_group (
                objective_id INT NOT NULL, 
                group_id INT NOT NULL, 
                INDEX IDX_FFDC9E073484933 (objective_id), 
                INDEX IDX_FFDC9E0FE54D947 (group_id), 
                PRIMARY KEY(objective_id, group_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            DROP FOREIGN KEY FK_EDBF854473484933
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_user 
            DROP FOREIGN KEY FK_6D032C1573484933
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_group 
            DROP FOREIGN KEY FK_FFDC9E073484933
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