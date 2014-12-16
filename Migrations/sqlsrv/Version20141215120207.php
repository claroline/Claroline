<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/15 12:02:09
 */
class Version20141215120207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD maxStorageSize NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD maxUploadResources INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD is_upload_destination BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP COLUMN is_upload_destination
        ");
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