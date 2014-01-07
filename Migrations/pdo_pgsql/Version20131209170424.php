<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

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
            ALTER TABLE claro_workspace ALTER is_public
            SET
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER displayable
            SET
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER self_registration
            SET
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER self_unregistration
            SET
                NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace ALTER is_public
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER displayable
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER self_registration
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER self_unregistration
            DROP NOT NULL
        ");
    }
}
