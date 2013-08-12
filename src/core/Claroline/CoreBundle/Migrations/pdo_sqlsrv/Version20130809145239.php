<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/09 02:52:41
 */
class Version20130809145239 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD target_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN resourceNode_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5E7F4AB8B87FAB32'
            ) 
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT IDX_5E7F4AB8B87FAB32 ELSE 
            DROP INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut (target_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP COLUMN target_id
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut ALTER COLUMN resourceNode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT FK_5E7F4AB8158E0B66
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5E7F4AB8158E0B66'
            ) 
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT IDX_5E7F4AB8158E0B66 ELSE 
            DROP INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_5E7F4AB8B87FAB32'
            ) 
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT UNIQ_5E7F4AB8B87FAB32 ELSE 
            DROP INDEX UNIQ_5E7F4AB8B87FAB32 ON claro_resource_shortcut
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut 
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
    }
}