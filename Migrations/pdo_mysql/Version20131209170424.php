<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

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
            ALTER TABLE claro_workspace CHANGE is_public is_public TINYINT(1) NOT NULL, 
            CHANGE displayable displayable TINYINT(1) NOT NULL, 
            CHANGE self_registration self_registration TINYINT(1) NOT NULL, 
            CHANGE self_unregistration self_unregistration TINYINT(1) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace CHANGE is_public is_public TINYINT(1) DEFAULT NULL, 
            CHANGE displayable displayable TINYINT(1) DEFAULT NULL, 
            CHANGE self_registration self_registration TINYINT(1) DEFAULT NULL, 
            CHANGE self_unregistration self_unregistration TINYINT(1) DEFAULT NULL
        ");
    }
}