<?php

namespace UJM\ExoBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/23 01:22:52
 */
class Version20150223132250 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_label 
            ADD COLUMN position_force SMALLINT DEFAULT NULL 
            ADD COLUMN ordre INTEGER NOT NULL WITH DEFAULT
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            ADD COLUMN shuffle SMALLINT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD COLUMN position_force SMALLINT DEFAULT NULL 
            ADD COLUMN ordre INTEGER NOT NULL WITH DEFAULT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            DROP COLUMN shuffle
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD (
                'REORG TABLE ujm_interaction_matching'
            )
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            DROP COLUMN position_force 
            DROP COLUMN ordre
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE ujm_label')
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            DROP COLUMN position_force 
            DROP COLUMN ordre
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE ujm_proposal')
        ");
    }
}