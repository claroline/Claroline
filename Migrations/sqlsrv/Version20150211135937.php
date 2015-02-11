<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/11 01:59:40
 */
class Version20150211135937 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD start_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD end_date DATETIME2(6)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD accessible_date BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD workspace_type INT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN start_date
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN end_date
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN accessible_date
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN workspace_type
        ");
    }
}