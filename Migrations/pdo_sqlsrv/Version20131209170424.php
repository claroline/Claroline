<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

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
            ALTER TABLE claro_workspace ALTER COLUMN is_public BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER COLUMN displayable BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER COLUMN self_registration BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER COLUMN self_unregistration BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace ALTER COLUMN is_public BIT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER COLUMN displayable BIT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER COLUMN self_registration BIT
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER COLUMN self_unregistration BIT
        ");
    }
}
