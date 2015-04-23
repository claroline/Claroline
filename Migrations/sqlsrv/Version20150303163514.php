<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/03 04:35:16
 */
class Version20150303163514 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FF2DE62210
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FFAA23F6C8
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_A76799FFAA23F6C8'
            ) 
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT UNIQ_A76799FFAA23F6C8 ELSE 
            DROP INDEX UNIQ_A76799FFAA23F6C8 ON claro_resource_node
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_A76799FF2DE62210'
            ) 
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT UNIQ_A76799FF2DE62210 ELSE 
            DROP INDEX UNIQ_A76799FF2DE62210 ON claro_resource_node
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD value NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN previous_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN next_id
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD previous_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD next_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN value
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF2DE62210 FOREIGN KEY (previous_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FFAA23F6C8 FOREIGN KEY (next_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FFAA23F6C8 ON claro_resource_node (next_id) 
            WHERE next_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FF2DE62210 ON claro_resource_node (previous_id) 
            WHERE previous_id IS NOT NULL
        ");
    }
}