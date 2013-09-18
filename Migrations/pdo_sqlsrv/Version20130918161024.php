<?php

namespace Innova\PathBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/18 04:10:24
 */
class Version20130918161024 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_path.workspace_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            DROP COLUMN [user]
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            DROP COLUMN edit_date
        ");
        $this->addSql("
            ALTER TABLE innova_path ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            DROP CONSTRAINT FK_CE19F05482D40A1F
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_CE19F05482D40A1F'
            ) 
            ALTER TABLE innova_path 
            DROP CONSTRAINT IDX_CE19F05482D40A1F ELSE 
            DROP INDEX IDX_CE19F05482D40A1F ON innova_path
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            ADD CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_CE19F054B87FAB32 ON innova_path (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'innova_path.resourcenode_id', 
            'workspace_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            ADD [user] NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            ADD edit_date DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_path ALTER COLUMN workspace_id INT
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            DROP CONSTRAINT FK_CE19F054B87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_CE19F054B87FAB32'
            ) 
            ALTER TABLE innova_path 
            DROP CONSTRAINT UNIQ_CE19F054B87FAB32 ELSE 
            DROP INDEX UNIQ_CE19F054B87FAB32 ON innova_path
        ");
        $this->addSql("
            ALTER TABLE innova_path 
            ADD CONSTRAINT FK_CE19F05482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CE19F05482D40A1F ON innova_path (workspace_id)
        ");
    }
}