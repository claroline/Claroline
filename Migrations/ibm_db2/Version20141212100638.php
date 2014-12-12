<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/12 10:06:40
 */
class Version20141212100638 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN maxStorageSize VARCHAR(255) NOT NULL WITH DEFAULT 
            ADD COLUMN maxUploadResources INTEGER NOT NULL WITH DEFAULT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN maxStorageSize 
            DROP COLUMN maxUploadResources
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD ('REORG TABLE claro_workspace')
        ");
    }
}