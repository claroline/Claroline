<?php

namespace Innova\PathBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/14 11:50:16
 */
class Version20140114115015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567DEDC9FF6
        ");
        $this->addSql("
            DROP INDEX IDX_86F48567DEDC9FF6
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            DROP COLUMN expanded 
            DROP COLUMN stepType_id ALTER duration duration TIMESTAMP(0) DEFAULT NULL RENAME COLUMN instructions TO description
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE innova_step')
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_step 
            ADD COLUMN expanded SMALLINT NOT NULL WITH DEFAULT 
            ADD COLUMN stepType_id INTEGER DEFAULT NULL ALTER duration duration TIMESTAMP(0) NOT NULL RENAME COLUMN description TO instructions
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE innova_step')
        ");
        $this->addSql("
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567DEDC9FF6 FOREIGN KEY (stepType_id) 
            REFERENCES innova_stepType (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_86F48567DEDC9FF6 ON innova_step (stepType_id)
        ");
    }
}