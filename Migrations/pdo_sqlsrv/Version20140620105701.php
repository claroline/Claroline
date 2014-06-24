<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/20 10:57:03
 */
class Version20140620105701 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_activity_parameters.where', 
            'activity_where', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters ALTER COLUMN activity_where NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_activity_parameters.activity_where', 
            '[where]', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters ALTER COLUMN [where] NVARCHAR(255)
        ");
    }
}