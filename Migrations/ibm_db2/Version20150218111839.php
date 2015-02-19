<?php

namespace HeVinci\CompetencyBundle\Migrations\ibm_db2;

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
            ADD COLUMN parent_id INTEGER DEFAULT NULL 
            ADD COLUMN lft INTEGER NOT NULL WITH DEFAULT 
            ADD COLUMN lvl INTEGER NOT NULL WITH DEFAULT 
            ADD COLUMN rgt INTEGER NOT NULL WITH DEFAULT 
            ADD COLUMN root INTEGER DEFAULT NULL
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
            DROP INDEX IDX_61ECD5E6727ACA70
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP COLUMN parent_id 
            DROP COLUMN lft 
            DROP COLUMN lvl 
            DROP COLUMN rgt 
            DROP COLUMN root
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD (
                'REORG TABLE hevinci_competency'
            )
        ");
    }
}