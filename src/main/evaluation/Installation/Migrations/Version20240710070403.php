<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/07/10 07:04:03
 */
final class Version20240710070403 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_evaluation_ability (
                id INT AUTO_INCREMENT NOT NULL, 
                skill_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_order INT NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_8266C68DD17F50A6 (uuid), 
                INDEX IDX_8266C68D5585C142 (skill_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_evaluation_skill (
                id INT AUTO_INCREMENT NOT NULL, 
                skills_framework_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_order INT NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_2BEBD56BD17F50A6 (uuid), 
                INDEX IDX_2BEBD56B64F84992 (skills_framework_id), 
                INDEX IDX_2BEBD56B727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_evaluation_skills_framework (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_E995162BD17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_ability 
            ADD CONSTRAINT FK_8266C68D5585C142 FOREIGN KEY (skill_id) 
            REFERENCES claro_evaluation_skill (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_skill 
            ADD CONSTRAINT FK_2BEBD56B64F84992 FOREIGN KEY (skills_framework_id) 
            REFERENCES claro_evaluation_skills_framework (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_skill 
            ADD CONSTRAINT FK_2BEBD56B727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_evaluation_skill (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_evaluation_ability 
            DROP FOREIGN KEY FK_8266C68D5585C142
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_skill 
            DROP FOREIGN KEY FK_2BEBD56B64F84992
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_skill 
            DROP FOREIGN KEY FK_2BEBD56B727ACA70
        ');
        $this->addSql('
            DROP TABLE claro_evaluation_ability
        ');
        $this->addSql('
            DROP TABLE claro_evaluation_skill
        ');
        $this->addSql('
            DROP TABLE claro_evaluation_skills_framework
        ');
    }
}
