<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

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
            DROP INDEX IDX_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_role 
            ADD personal_workspace_creation_enabled BOOLEAN NOT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FFAA23F6C8
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF2DE62210
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_12EEC186B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_E2EE25E281C06096
        ");
        $this->addSql("
            DROP INDEX IDX_EA81C80BB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_5D9559DCB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            DROP INDEX IDX_E4A67CACB87FAB32
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
            DROP personal_workspace_creation_enabled
        ");
        $this->addSql("
            CREATE INDEX IDX_5D9559DCB87FAB32 ON claro_text (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EB8D285282D40A1F ON claro_user (workspace_id)
        ");
    }
}