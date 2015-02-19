<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_mysql;

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
            ADD parent_id INT DEFAULT NULL, 
            ADD lft INT NOT NULL, 
            ADD lvl INT NOT NULL, 
            ADD rgt INT NOT NULL, 
            ADD root INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_61ECD5E6727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_61ECD5E6727ACA70 ON hevinci_competency
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP parent_id, 
            DROP lft, 
            DROP lvl, 
            DROP rgt, 
            DROP root
        ");
    }
}