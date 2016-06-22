<?php

namespace Innova\CollecticielBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/06/14 05:39:15
 */
class Version20160614173913 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_choice_criteria (
                id INT AUTO_INCREMENT NOT NULL, 
                criteria_id INT NOT NULL, 
                choice_text LONGTEXT DEFAULT NULL, 
                INDEX IDX_2EC94D86990BEA15 (criteria_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_choice_criteria 
            ADD CONSTRAINT FK_2EC94D86990BEA15 FOREIGN KEY (criteria_id) 
            REFERENCES innova_collecticielbundle_grading_criteria (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE innova_collecticielbundle_choice_criteria
        ');
    }
}
