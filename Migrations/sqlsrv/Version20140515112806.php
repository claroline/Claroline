<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

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
            ADD workspace_id INT
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
            DROP COLUMN workspace_id
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP CONSTRAINT FK_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_C8EFD7EF82D40A1F'
            ) 
            ALTER TABLE claro_workspace_tag 
            DROP CONSTRAINT IDX_C8EFD7EF82D40A1F ELSE 
            DROP INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag
        ");
    }
}