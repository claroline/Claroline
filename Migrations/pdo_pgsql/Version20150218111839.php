<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/18 11:18:40
 */
class Version20150218111839 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD parent_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD lft INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD lvl INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD rgt INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD root INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD CONSTRAINT FK_61ECD5E6727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_61ECD5E6727ACA70 ON hevinci_competency (parent_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP CONSTRAINT FK_61ECD5E6727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_61ECD5E6727ACA70
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP parent_id
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP lft
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP lvl
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP rgt
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP root
        ");
    }
}