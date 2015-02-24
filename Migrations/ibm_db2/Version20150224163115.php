<?php

namespace HeVinci\CompetencyBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/24 04:31:16
 */
class Version20150224163115 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_ability 
            ADD COLUMN minActivityCount INTEGER NOT NULL WITH DEFAULT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_ability 
            DROP COLUMN minActivityCount
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE hevinci_ability')
        ");
    }
}