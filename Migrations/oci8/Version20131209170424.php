<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/12/09 05:04:25
 */
class Version20131209170424 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace MODIFY (
                is_public NUMBER(1) NOT NULL, 
                displayable NUMBER(1) NOT NULL, 
                self_registration NUMBER(1) NOT NULL, 
                self_unregistration NUMBER(1) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace MODIFY (
                is_public NUMBER(1) DEFAULT NULL, 
                displayable NUMBER(1) DEFAULT NULL, 
                self_registration NUMBER(1) DEFAULT NULL, 
                self_unregistration NUMBER(1) DEFAULT NULL
            )
        ");
    }
}