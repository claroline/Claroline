<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/11 02:09:54
 */
class Version20150211140951 extends AbstractMigration
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
            ADD is_access_date BIT NOT NULL
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
            DROP COLUMN is_access_date
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN workspace_type
        ");
    }
}