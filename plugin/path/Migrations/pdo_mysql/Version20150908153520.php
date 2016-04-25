<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/09/08 03:35:21
 */
class Version20150908153520 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_stepcondition (
                id INT AUTO_INCREMENT NOT NULL, 
                step_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_C6D7069F73B21E9C (step_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_stepcondition_criteriagroup (
                id INT AUTO_INCREMENT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                stepcondition_id INT DEFAULT NULL, 
                lvl INT NOT NULL, 
                criteriagroup_order INT NOT NULL, 
                INDEX IDX_F33A94EA727ACA70 (parent_id), 
                INDEX IDX_F33A94EAD71D3B68 (stepcondition_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE StepConditionsGroup (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_stepcondition_criterion (
                id INT AUTO_INCREMENT NOT NULL, 
                criteriagroup_id INT DEFAULT NULL, 
                ctype VARCHAR(255) NOT NULL, 
                data VARCHAR(255) NOT NULL, 
                INDEX IDX_D1B1FC36E10D333F (criteriagroup_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_stepcondition 
            ADD CONSTRAINT FK_C6D7069F73B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id)
        ');
        $this->addSql('
            ALTER TABLE innova_stepcondition_criteriagroup 
            ADD CONSTRAINT FK_F33A94EA727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES innova_stepcondition_criteriagroup (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_stepcondition_criteriagroup 
            ADD CONSTRAINT FK_F33A94EAD71D3B68 FOREIGN KEY (stepcondition_id) 
            REFERENCES innova_stepcondition (id)
        ');
        $this->addSql('
            ALTER TABLE innova_stepcondition_criterion 
            ADD CONSTRAINT FK_D1B1FC36E10D333F FOREIGN KEY (criteriagroup_id) 
            REFERENCES innova_stepcondition_criteriagroup (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_stepcondition_criteriagroup 
            DROP FOREIGN KEY FK_F33A94EAD71D3B68
        ');
        $this->addSql('
            ALTER TABLE innova_stepcondition_criteriagroup 
            DROP FOREIGN KEY FK_F33A94EA727ACA70
        ');
        $this->addSql('
            ALTER TABLE innova_stepcondition_criterion 
            DROP FOREIGN KEY FK_D1B1FC36E10D333F
        ');
        $this->addSql('
            DROP TABLE innova_stepcondition
        ');
        $this->addSql('
            DROP TABLE innova_stepcondition_criteriagroup
        ');
        $this->addSql('
            DROP TABLE StepConditionsGroup
        ');
        $this->addSql('
            DROP TABLE innova_stepcondition_criterion
        ');
    }
}
