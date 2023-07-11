<?php

namespace HeVinci\CompetencyBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 02:05:10
 */
final class Version20190909144722 extends AbstractMigration
{
    public function up(Schema $schema): void
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_competency_resource (
                competency_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                INDEX IDX_922821F3FB9F58C (competency_id), 
                INDEX IDX_922821F377C292AE (resourcenode_id), 
                PRIMARY KEY(competency_id, resourcenode_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_scale (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_D3477F405E237E06 (name), 
                UNIQUE INDEX UNIQ_D3477F40D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_ability_resource (
                ability_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                INDEX IDX_563CD07E8016D8B2 (ability_id), 
                INDEX IDX_563CD07E77C292AE (resourcenode_id), 
                PRIMARY KEY(ability_id, resourcenode_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE hevinci_competency 
            DROP FOREIGN KEY FK_61ECD5E6F73142C2
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency 
            DROP FOREIGN KEY FK_61ECD5E6727ACA70
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_resource 
            DROP FOREIGN KEY FK_922821F3FB9F58C
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_resource 
            DROP FOREIGN KEY FK_922821F377C292AE
        ');
        $this->addSql('
            ALTER TABLE hevinci_level 
            DROP FOREIGN KEY FK_A5EB96D7F73142C2
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_resource 
            DROP FOREIGN KEY FK_563CD07E8016D8B2
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_resource 
            DROP FOREIGN KEY FK_563CD07E77C292AE
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            DROP FOREIGN KEY FK_38178A41FB9F58C
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            DROP FOREIGN KEY FK_38178A418016D8B2
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            DROP FOREIGN KEY FK_38178A415FB14BA7
        ');
        $this->addSql('
            DROP TABLE hevinci_competency
        ');
        $this->addSql('
            DROP TABLE hevinci_competency_resource
        ');
        $this->addSql('
            DROP TABLE hevinci_level
        ');
        $this->addSql('
            DROP TABLE hevinci_scale
        ');
        $this->addSql('
            DROP TABLE hevinci_ability
        ');
        $this->addSql('
            DROP TABLE hevinci_ability_resource
        ');
        $this->addSql('
            DROP TABLE hevinci_competency_ability
        ');
    }
}
