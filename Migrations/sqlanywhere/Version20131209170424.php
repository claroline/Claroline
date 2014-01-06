<?php

namespace Claroline\CoreBundle\Migrations\sqlanywhere;

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
            ALTER TABLE claro_workspace ALTER is_public BIT NOT NULL,
            ALTER displayable BIT NOT NULL,
            ALTER self_registration BIT NOT NULL,
            ALTER self_unregistration BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace ALTER is_public BIT NULL DEFAULT NULL,
            ALTER displayable BIT NULL DEFAULT NULL,
            ALTER self_registration BIT NULL DEFAULT NULL,
            ALTER self_unregistration BIT NULL DEFAULT NULL
        ");
    }
}
