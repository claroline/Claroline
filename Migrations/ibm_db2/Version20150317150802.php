<?php

namespace HeVinci\CompetencyBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/17 03:08:04
 */
class Version20150317150802 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD COLUMN activityCount INTEGER NOT NULL WITH DEFAULT
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability 
            ADD COLUMN activityCount INTEGER NOT NULL WITH DEFAULT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_ability 
            DROP COLUMN activityCount
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE hevinci_ability')
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP COLUMN activityCount
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD (
                'REORG TABLE hevinci_competency'
            )
        ");
    }
}