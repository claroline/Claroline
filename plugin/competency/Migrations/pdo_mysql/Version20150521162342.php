<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/05/21 04:23:43
 */
class Version20150521162342 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_objective_progress (
                id INT AUTO_INCREMENT NOT NULL, 
                objective_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                objective_name VARCHAR(255) NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                INDEX IDX_CAC2DC3873484933 (objective_id), 
                INDEX IDX_CAC2DC38A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_competency_progress_log (
                id INT AUTO_INCREMENT NOT NULL, 
                competency_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                level_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                competency_name VARCHAR(255) NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                level_name VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_8522FF2AFB9F58C (competency_id), 
                INDEX IDX_8522FF2AA76ED395 (user_id), 
                INDEX IDX_8522FF2A5FB14BA7 (level_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_progress_log (
                id INT AUTO_INCREMENT NOT NULL, 
                objective_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                objective_name VARCHAR(255) NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                INDEX IDX_F125F34773484933 (objective_id), 
                INDEX IDX_F125F347A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_user_progress_log (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                INDEX IDX_5125DF35A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_user_progress (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                INDEX IDX_53E81580A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_progress 
            ADD CONSTRAINT FK_CAC2DC3873484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_progress 
            ADD CONSTRAINT FK_CAC2DC38A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress_log 
            ADD CONSTRAINT FK_8522FF2AFB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress_log 
            ADD CONSTRAINT FK_8522FF2AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress_log 
            ADD CONSTRAINT FK_8522FF2A5FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_progress_log 
            ADD CONSTRAINT FK_F125F34773484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_progress_log 
            ADD CONSTRAINT FK_F125F347A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_user_progress_log 
            ADD CONSTRAINT FK_5125DF35A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_user_progress 
            ADD CONSTRAINT FK_53E81580A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_progress 
            ADD ability_name VARCHAR(255) NOT NULL, 
            ADD user_name VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD percentage INT NOT NULL, 
            ADD user_name VARCHAR(255) NOT NULL, 
            ADD level_name VARCHAR(255) DEFAULT NULL, 
            CHANGE type competency_name VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_objective_progress
        ");
        $this->addSql("
            DROP TABLE hevinci_competency_progress_log
        ");
        $this->addSql("
            DROP TABLE hevinci_objective_progress_log
        ");
        $this->addSql("
            DROP TABLE hevinci_user_progress_log
        ");
        $this->addSql("
            DROP TABLE hevinci_user_progress
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_progress 
            DROP ability_name, 
            DROP user_name
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
            DROP percentage, 
            DROP competency_name, 
            DROP user_name, 
            DROP level_name
        ");
    }
}