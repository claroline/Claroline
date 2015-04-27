<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/27 11:24:02
 */
class Version20150427112359 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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