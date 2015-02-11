<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

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
            ADD (
                start_date TIMESTAMP(0) DEFAULT NULL NULL, 
                end_date TIMESTAMP(0) DEFAULT NULL NULL, 
                is_access_date NUMBER(1) NOT NULL, 
                workspace_type NUMBER(10) DEFAULT NULL NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP (
                start_date, end_date, is_access_date, 
                workspace_type
            )
        ");
    }
}