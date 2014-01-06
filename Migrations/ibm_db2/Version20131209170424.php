<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

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
            ALTER TABLE claro_workspace ALTER is_public is_public SMALLINT NOT NULL ALTER displayable displayable SMALLINT NOT NULL ALTER self_registration self_registration SMALLINT NOT NULL ALTER self_unregistration self_unregistration SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace ALTER is_public is_public SMALLINT DEFAULT NULL ALTER displayable displayable SMALLINT DEFAULT NULL ALTER self_registration self_registration SMALLINT DEFAULT NULL ALTER self_unregistration self_unregistration SMALLINT DEFAULT NULL
        ");
    }
}
