<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/11 02:09:53
 */
class Version20150211140951 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD is_access_date BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD workspace_type INT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP start_date
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP end_date
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP is_access_date
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP workspace_type
        ");
    }
}