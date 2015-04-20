<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/15 11:03:51
 */
class Version20150415110347 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FF460F904B
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_A76799FF460F904B'
            ) 
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT IDX_A76799FF460F904B ELSE 
            DROP INDEX IDX_A76799FF460F904B ON claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD license NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD author NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD active BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT DF_A76799FF_4B1EFC02 DEFAULT '1' FOR active
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN license_id
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD license_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN license
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN author
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN active
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id) 
            REFERENCES claro_license (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
        ");
    }
}