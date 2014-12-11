<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/11 10:31:13
 */
class Version20141211103112 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD maxStorageSize INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD maxUploadResources INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN maxStorageSize
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN maxUploadResources
        ");
    }
}