<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/15 11:28:07
 */
class Version20140515112806 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD workspace_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD CONSTRAINT FK_C8EFD7EF82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP FOREIGN KEY FK_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP workspace_id
        ");
    }
}