<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/28 12:01:19
 */
class Version20140528120118 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD accessible_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD accessible_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP CONSTRAINT FK_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP workspace_id
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP accessible_from
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP accessible_to
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD workspace_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD CONSTRAINT FK_C8EFD7EF82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag (workspace_id)
        ");
    }
}