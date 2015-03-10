<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/09 10:53:52
 */
class Version20150309105351 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER COLUMN \"value\" 
            SET 
                DATA TYPE INTEGER
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD (
                'REORG TABLE claro_resource_node'
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER COLUMN \"value\" 
            SET 
                DATA TYPE VARCHAR(255)
        ");
        $this->addSql("
            CALL SYSPROC.ADMIN_CMD (
                'REORG TABLE claro_resource_node'
            )
        ");
    }
}