<?php

namespace HeVinci\CompetencyBundle\Migrations\oci8;

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
            ADD (
                parent_id NUMBER(10) DEFAULT NULL NULL, 
                lft NUMBER(10) NOT NULL, 
                lvl NUMBER(10) NOT NULL, 
                rgt NUMBER(10) NOT NULL, 
                root NUMBER(10) DEFAULT NULL NULL
            )
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
            DROP INDEX IDX_61ECD5E6727ACA70
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP (parent_id, lft, lvl, rgt, root)
        ");
    }
}