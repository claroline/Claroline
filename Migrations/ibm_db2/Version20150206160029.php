<?php

namespace HeVinci\CompetencyBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/06 04:00:30
 */
class Version20150206160029 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_level ALTER COLUMN scale_id 
            SET 
                NOT NULL
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE hevinci_level')
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_level ALTER COLUMN scale_id 
            DROP NOT NULL
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE hevinci_level')
        ");
    }
}