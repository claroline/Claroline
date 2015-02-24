<?php

namespace HeVinci\CompetencyBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/23 03:11:11
 */
class Version20150223151110 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_competency_ability (
                id INT AUTO_INCREMENT NOT NULL, 
                competency_id INT NOT NULL, 
                ability_id INT NOT NULL, 
                level_id INT NOT NULL, 
                INDEX IDX_38178A41FB9F58C (competency_id), 
                INDEX IDX_38178A418016D8B2 (ability_id), 
                INDEX IDX_38178A415FB14BA7 (level_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_ability (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            ADD CONSTRAINT FK_38178A41FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            ADD CONSTRAINT FK_38178A418016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            ADD CONSTRAINT FK_38178A415FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_competency_ability 
            DROP FOREIGN KEY FK_38178A418016D8B2
        ");
        $this->addSql("
            DROP TABLE hevinci_competency_ability
        ");
        $this->addSql("
            DROP TABLE hevinci_ability
        ");
    }
}