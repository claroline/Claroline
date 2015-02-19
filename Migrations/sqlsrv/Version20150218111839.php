<?php

namespace HeVinci\CompetencyBundle\Migrations\sqlsrv;

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
            ADD parent_id INT
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
            ADD root INT
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD CONSTRAINT FK_61ECD5E6727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
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
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_61ECD5E6727ACA70'
            ) 
            ALTER TABLE hevinci_competency 
            DROP CONSTRAINT IDX_61ECD5E6727ACA70 ELSE 
            DROP INDEX IDX_61ECD5E6727ACA70 ON hevinci_competency
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP COLUMN parent_id
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP COLUMN lft
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP COLUMN lvl
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP COLUMN rgt
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP COLUMN root
        ");
    }
}