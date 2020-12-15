<?php

namespace HeVinci\CompetencyBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:37:11
 */
class Version20190909144722 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE hevinci_competency (
                id INT AUTO_INCREMENT NOT NULL, 
                scale_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                resourceCount INT NOT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_61ECD5E6D17F50A6 (uuid), 
                INDEX IDX_61ECD5E6F73142C2 (scale_id), 
                INDEX IDX_61ECD5E6727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_competency_resource (
                competency_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                INDEX IDX_922821F3FB9F58C (competency_id), 
                INDEX IDX_922821F377C292AE (resourcenode_id), 
                PRIMARY KEY(competency_id, resourcenode_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_scale (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_D3477F405E237E06 (name), 
                UNIQUE INDEX UNIQ_D3477F40D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_competency_ability (
                id INT AUTO_INCREMENT NOT NULL, 
                competency_id INT NOT NULL, 
                ability_id INT NOT NULL, 
                level_id INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_38178A41D17F50A6 (uuid), 
                INDEX IDX_38178A41FB9F58C (competency_id), 
                INDEX IDX_38178A418016D8B2 (ability_id), 
                INDEX IDX_38178A415FB14BA7 (level_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_level (
                id INT AUTO_INCREMENT NOT NULL, 
                scale_id INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                value INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_A5EB96D7D17F50A6 (uuid), 
                INDEX IDX_A5EB96D7F73142C2 (scale_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_ability (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                minResourceCount INT NOT NULL, 
                minEvaluatedResourceCount INT NOT NULL, 
                resourceCount INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_11E77B9D5E237E06 (name), 
                UNIQUE INDEX UNIQ_11E77B9DD17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_ability_resource (
                ability_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                INDEX IDX_563CD07E8016D8B2 (ability_id), 
                INDEX IDX_563CD07E77C292AE (resourcenode_id), 
                PRIMARY KEY(ability_id, resourcenode_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_learning_objective (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_10D9D6545E237E06 (name), 
                UNIQUE INDEX UNIQ_10D9D654D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_objective_user (
                objective_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_6D032C1573484933 (objective_id), 
                INDEX IDX_6D032C15A76ED395 (user_id), 
                PRIMARY KEY(objective_id, user_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_objective_group (
                objective_id INT NOT NULL, 
                group_id INT NOT NULL, 
                INDEX IDX_FFDC9E073484933 (objective_id), 
                INDEX IDX_FFDC9E0FE54D947 (group_id), 
                PRIMARY KEY(objective_id, group_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_objective_competency (
                id INT AUTO_INCREMENT NOT NULL, 
                objective_id INT NOT NULL, 
                competency_id INT NOT NULL, 
                level_id INT NOT NULL, 
                framework_id INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_EDBF8544D17F50A6 (uuid), 
                INDEX IDX_EDBF854473484933 (objective_id), 
                INDEX IDX_EDBF8544FB9F58C (competency_id), 
                INDEX IDX_EDBF85445FB14BA7 (level_id), 
                INDEX IDX_EDBF854437AECF72 (framework_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE hevinci_ability_progress (
                id INT AUTO_INCREMENT NOT NULL, 
                ability_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                passed_resource_ids LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', 
                failed_resource_ids LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', 
                passed_resource_count INT NOT NULL, 
                status VARCHAR(255) NOT NULL, 
                ability_name VARCHAR(255) NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_C8ACD62ED17F50A6 (uuid), 
                INDEX IDX_C8ACD62E8016D8B2 (ability_id), 
                INDEX IDX_C8ACD62EA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE hevinci_competency_progress (
                id INT AUTO_INCREMENT NOT NULL, 
                competency_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                level_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                resource_id INT DEFAULT NULL, 
                percentage INT NOT NULL, 
                competency_name VARCHAR(255) NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                level_name VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_CB827A3D17F50A6 (uuid), 
                INDEX IDX_CB827A3FB9F58C (competency_id), 
                INDEX IDX_CB827A3A76ED395 (user_id), 
                INDEX IDX_CB827A35FB14BA7 (level_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
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
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_8522FF2AD17F50A6 (uuid), 
                INDEX IDX_8522FF2AFB9F58C (competency_id), 
                INDEX IDX_8522FF2AA76ED395 (user_id), 
                INDEX IDX_8522FF2A5FB14BA7 (level_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_objective_progress (
                id INT AUTO_INCREMENT NOT NULL, 
                objective_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                objective_name VARCHAR(255) NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_CAC2DC38D17F50A6 (uuid), 
                INDEX IDX_CAC2DC3873484933 (objective_id), 
                INDEX IDX_CAC2DC38A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_objective_progress_log (
                id INT AUTO_INCREMENT NOT NULL, 
                objective_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                objective_name VARCHAR(255) NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_F125F347D17F50A6 (uuid), 
                INDEX IDX_F125F34773484933 (objective_id), 
                INDEX IDX_F125F347A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_user_progress (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_53E81580D17F50A6 (uuid), 
                INDEX IDX_53E81580A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_user_progress_log (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_5125DF35D17F50A6 (uuid), 
                INDEX IDX_5125DF35A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency 
            ADD CONSTRAINT FK_61ECD5E6F73142C2 FOREIGN KEY (scale_id) 
            REFERENCES hevinci_scale (id)
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency 
            ADD CONSTRAINT FK_61ECD5E6727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_resource 
            ADD CONSTRAINT FK_922821F3FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_resource 
            ADD CONSTRAINT FK_922821F377C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            ADD CONSTRAINT FK_38178A41FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            ADD CONSTRAINT FK_38178A418016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            ADD CONSTRAINT FK_38178A415FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_level 
            ADD CONSTRAINT FK_A5EB96D7F73142C2 FOREIGN KEY (scale_id) 
            REFERENCES hevinci_scale (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_resource 
            ADD CONSTRAINT FK_563CD07E8016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_resource 
            ADD CONSTRAINT FK_563CD07E77C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_user 
            ADD CONSTRAINT FK_6D032C1573484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_user 
            ADD CONSTRAINT FK_6D032C15A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_group 
            ADD CONSTRAINT FK_FFDC9E073484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_group 
            ADD CONSTRAINT FK_FFDC9E0FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF854473484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF8544FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF85445FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF854437AECF72 FOREIGN KEY (framework_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_progress 
            ADD CONSTRAINT FK_C8ACD62E8016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_progress 
            ADD CONSTRAINT FK_C8ACD62EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A3FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A35FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log 
            ADD CONSTRAINT FK_8522FF2AFB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log 
            ADD CONSTRAINT FK_8522FF2AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log 
            ADD CONSTRAINT FK_8522FF2A5FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress 
            ADD CONSTRAINT FK_CAC2DC3873484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress 
            ADD CONSTRAINT FK_CAC2DC38A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress_log 
            ADD CONSTRAINT FK_F125F34773484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress_log 
            ADD CONSTRAINT FK_F125F347A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_user_progress 
            ADD CONSTRAINT FK_53E81580A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_user_progress_log 
            ADD CONSTRAINT FK_5125DF35A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_competency 
            DROP FOREIGN KEY FK_61ECD5E6727ACA70
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_resource 
            DROP FOREIGN KEY FK_922821F3FB9F58C
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            DROP FOREIGN KEY FK_38178A41FB9F58C
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            DROP FOREIGN KEY FK_EDBF8544FB9F58C
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            DROP FOREIGN KEY FK_EDBF854437AECF72
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress 
            DROP FOREIGN KEY FK_CB827A3FB9F58C
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log 
            DROP FOREIGN KEY FK_8522FF2AFB9F58C
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency 
            DROP FOREIGN KEY FK_61ECD5E6F73142C2
        ');
        $this->addSql('
            ALTER TABLE hevinci_level 
            DROP FOREIGN KEY FK_A5EB96D7F73142C2
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            DROP FOREIGN KEY FK_38178A415FB14BA7
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            DROP FOREIGN KEY FK_EDBF85445FB14BA7
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress 
            DROP FOREIGN KEY FK_CB827A35FB14BA7
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log 
            DROP FOREIGN KEY FK_8522FF2A5FB14BA7
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            DROP FOREIGN KEY FK_38178A418016D8B2
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_resource 
            DROP FOREIGN KEY FK_563CD07E8016D8B2
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_progress 
            DROP FOREIGN KEY FK_C8ACD62E8016D8B2
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_user 
            DROP FOREIGN KEY FK_6D032C1573484933
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_group 
            DROP FOREIGN KEY FK_FFDC9E073484933
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            DROP FOREIGN KEY FK_EDBF854473484933
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress 
            DROP FOREIGN KEY FK_CAC2DC3873484933
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress_log 
            DROP FOREIGN KEY FK_F125F34773484933
        ');
        $this->addSql('
            DROP TABLE hevinci_competency
        ');
        $this->addSql('
            DROP TABLE hevinci_competency_resource
        ');
        $this->addSql('
            DROP TABLE hevinci_scale
        ');
        $this->addSql('
            DROP TABLE hevinci_competency_ability
        ');
        $this->addSql('
            DROP TABLE hevinci_level
        ');
        $this->addSql('
            DROP TABLE hevinci_ability
        ');
        $this->addSql('
            DROP TABLE hevinci_ability_resource
        ');
        $this->addSql('
            DROP TABLE hevinci_learning_objective
        ');
        $this->addSql('
            DROP TABLE hevinci_objective_user
        ');
        $this->addSql('
            DROP TABLE hevinci_objective_group
        ');
        $this->addSql('
            DROP TABLE hevinci_objective_competency
        ');
        $this->addSql('
            DROP TABLE hevinci_ability_progress
        ');
        $this->addSql('
            DROP TABLE hevinci_competency_progress
        ');
        $this->addSql('
            DROP TABLE hevinci_competency_progress_log
        ');
        $this->addSql('
            DROP TABLE hevinci_objective_progress
        ');
        $this->addSql('
            DROP TABLE hevinci_objective_progress_log
        ');
        $this->addSql('
            DROP TABLE hevinci_user_progress
        ');
        $this->addSql('
            DROP TABLE hevinci_user_progress_log
        ');
    }
}
