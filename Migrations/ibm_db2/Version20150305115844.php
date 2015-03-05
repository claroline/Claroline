<?php

namespace HeVinci\CompetencyBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/05 11:58:45
 */
class Version20150305115844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_scale 
            DROP COLUMN is_locked
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE hevinci_scale')
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_scale 
            ADD COLUMN is_locked SMALLINT NOT NULL WITH DEFAULT
        ");
    }
}