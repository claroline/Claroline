<?php

namespace HeVinci\CompetencyBundle\Migrations\sqlanywhere;

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
                id INT IDENTITY NOT NULL, 
                scale_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description TEXT DEFAULT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_61ECD5E6F73142C2 ON hevinci_competency (scale_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_scale (
                id INT IDENTITY NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE hevinci_level (
                id INT IDENTITY NOT NULL, 
                scale_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                value INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A5EB96D7F73142C2 ON hevinci_level (scale_id)
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