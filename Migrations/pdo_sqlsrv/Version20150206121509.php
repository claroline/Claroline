<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/06 12:15:11
 */
class Version20150206121509 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_EB8D285282D40A1F'
            ) 
            ALTER TABLE claro_user 
            DROP CONSTRAINT IDX_EB8D285282D40A1F ELSE 
            DROP INDEX IDX_EB8D285282D40A1F ON claro_user
        ");
        $this->addSql("
            ALTER TABLE claro_role 
            ADD personal_workspace_creation_enabled BIT NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_A76799FFAA23F6C8'
            ) 
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT IDX_A76799FFAA23F6C8 ELSE 
            DROP INDEX IDX_A76799FFAA23F6C8 ON claro_resource_node
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_A76799FF2DE62210'
            ) 
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT IDX_A76799FF2DE62210 ELSE 
            DROP INDEX IDX_A76799FF2DE62210 ON claro_resource_node
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
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_12EEC186B87FAB32'
            ) 
            ALTER TABLE claro_directory 
            DROP CONSTRAINT IDX_12EEC186B87FAB32 ELSE 
            DROP INDEX IDX_12EEC186B87FAB32 ON claro_directory
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_E2EE25E281C06096'
            ) 
            ALTER TABLE claro_activity_parameters 
            DROP CONSTRAINT IDX_E2EE25E281C06096 ELSE 
            DROP INDEX IDX_E2EE25E281C06096 ON claro_activity_parameters
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_EA81C80BB87FAB32'
            ) 
            ALTER TABLE claro_file 
            DROP CONSTRAINT IDX_EA81C80BB87FAB32 ELSE 
            DROP INDEX IDX_EA81C80BB87FAB32 ON claro_file
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5D9559DCB87FAB32'
            ) 
            ALTER TABLE claro_text 
            DROP CONSTRAINT IDX_5D9559DCB87FAB32 ELSE 
            DROP INDEX IDX_5D9559DCB87FAB32 ON claro_text
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_E4A67CAC88BD9C1F'
            ) 
            ALTER TABLE claro_activity 
            DROP CONSTRAINT IDX_E4A67CAC88BD9C1F ELSE 
            DROP INDEX IDX_E4A67CAC88BD9C1F ON claro_activity
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_E4A67CACB87FAB32'
            ) 
            ALTER TABLE claro_activity 
            DROP CONSTRAINT IDX_E4A67CACB87FAB32 ELSE 
            DROP INDEX IDX_E4A67CACB87FAB32 ON claro_activity
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE INDEX IDX_E4A67CAC88BD9C1F ON claro_activity (parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E2EE25E281C06096 ON claro_activity_parameters (activity_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_12EEC186B87FAB32 ON claro_directory (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FFAA23F6C8 ON claro_resource_node (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF2DE62210 ON claro_resource_node (previous_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_role 
            DROP COLUMN personal_workspace_creation_enabled
        ");
        $this->addSql("
            CREATE INDEX IDX_5D9559DCB87FAB32 ON claro_text (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EB8D285282D40A1F ON claro_user (workspace_id)
        ");
    }
}