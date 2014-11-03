<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/03 12:02:37
 */
class Version20141103120235 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_resource_node.is_visible', 
            'published', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT DF_A76799FF_6C8C6E91
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER COLUMN published BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT DF_A76799FF_683C6017 DEFAULT '1' FOR published
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_resource_node.published', 
            'is_visible', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT DF_A76799FF_683C6017
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node ALTER COLUMN is_visible BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT DF_A76799FF_6C8C6E91 DEFAULT '1' FOR is_visible
        ");
    }
}