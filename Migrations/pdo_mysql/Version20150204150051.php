<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/04 03:00:52
 */
class Version20150204150051 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_competency (
                id INT AUTO_INCREMENT NOT NULL, 
                scale_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                INDEX IDX_61ECD5E6F73142C2 (scale_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_scale (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE hevinci_level (
                id INT AUTO_INCREMENT NOT NULL, 
                scale_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                value INT NOT NULL, 
                INDEX IDX_A5EB96D7F73142C2 (scale_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD CONSTRAINT FK_61ECD5E6F73142C2 FOREIGN KEY (scale_id) 
            REFERENCES hevinci_scale (id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_level 
            ADD CONSTRAINT FK_A5EB96D7F73142C2 FOREIGN KEY (scale_id) 
            REFERENCES hevinci_scale (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP FOREIGN KEY FK_61ECD5E6F73142C2
        ");
        $this->addSql("
            ALTER TABLE hevinci_level 
            DROP FOREIGN KEY FK_A5EB96D7F73142C2
        ");
        $this->addSql("
            DROP TABLE hevinci_competency
        ");
        $this->addSql("
            DROP TABLE hevinci_scale
        ");
        $this->addSql("
            DROP TABLE hevinci_level
        ");
    }
}