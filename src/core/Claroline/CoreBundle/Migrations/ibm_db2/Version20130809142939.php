<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/09 02:29:40
 */
class Version20130809142939 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD COLUMN class VARCHAR(256) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP COLUMN parent_id 
            DROP COLUMN class
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP FOREIGN KEY FK_AEC62693727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_AEC62693727ACA70
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN class
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD COLUMN parent_id INTEGER DEFAULT NULL 
            ADD COLUMN class VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD CONSTRAINT FK_AEC62693727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693727ACA70 ON claro_resource_type (parent_id)
        ");
    }
}