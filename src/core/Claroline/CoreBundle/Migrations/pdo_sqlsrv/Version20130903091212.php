<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 09:12:15
 */
class Version20130903091212 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log 
            ADD is_displayed_in_admin BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            ADD is_displayed_in_workspace BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP COLUMN child_type
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP COLUMN child_action
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log 
            ADD child_type NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            ADD child_action NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP COLUMN is_displayed_in_admin
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP COLUMN is_displayed_in_workspace
        ");
    }
}