<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

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
            ADD class NVARCHAR(256) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP COLUMN parent_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP COLUMN class
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP CONSTRAINT FK_AEC62693727ACA70
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_AEC62693727ACA70'
            ) 
            ALTER TABLE claro_resource_type 
            DROP CONSTRAINT IDX_AEC62693727ACA70 ELSE 
            DROP INDEX IDX_AEC62693727ACA70 ON claro_resource_type
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
            ADD parent_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD class NVARCHAR(255)
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