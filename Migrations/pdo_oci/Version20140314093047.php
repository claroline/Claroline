<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/14 09:30:47
 */
class Version20140314093047 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD (
                is_locked_for_admin NUMBER(1) NOT NULL, 
                is_anonymous_excluded NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD (
                defaultMask NUMBER(10) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP (defaultMask)
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP (
                is_locked_for_admin, is_anonymous_excluded
            )
        ");
    }
}