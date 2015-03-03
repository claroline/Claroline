<?php

namespace UJM\ExoBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/03 01:32:22
 */
class Version20150303133220 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_category 
            ADD COLUMN locker SMALLINT NOT NULL WITH DEFAULT
        ");
        $this->addSql("
            RENAME INDEX idx_b797c100fab79c10 TO IDX_2672B44BFAB79C10
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_category 
            DROP COLUMN locker
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE ujm_category')
        ");
        $this->addSql("
            RENAME INDEX idx_2672b44bfab79c10 TO IDX_B797C100FAB79C10
        ");
    }
}