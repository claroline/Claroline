<?php

namespace Claroline\CoreBundle\Migrations\sqlanywhere;

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
            ADD start_date DATETIME DEFAULT NULL, 
            ADD end_date DATETIME DEFAULT NULL, 
            ADD accessible_date BIT NOT NULL, 
            ADD workspace_type INT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP start_date, 
            DROP end_date, 
            DROP accessible_date, 
            DROP workspace_type
        ");
    }
}