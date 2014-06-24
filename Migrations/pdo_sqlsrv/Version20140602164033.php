<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/02 04:40:35
 */
class Version20140602164033 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD accessible_from DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD accessible_until DATETIME2(6)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN accessible_from
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN accessible_until
        ");
    }
}