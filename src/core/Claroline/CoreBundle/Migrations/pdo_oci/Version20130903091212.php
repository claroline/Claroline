<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 09:12:14
 */
class Version20130903091212 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log 
            ADD (
                is_displayed_in_admin NUMBER(1) NOT NULL, 
                is_displayed_in_workspace NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP (child_type, child_action)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log 
            ADD (
                child_type VARCHAR2(255) DEFAULT NULL, 
                child_action VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_log 
            DROP (
                is_displayed_in_admin, is_displayed_in_workspace
            )
        ");
    }
}