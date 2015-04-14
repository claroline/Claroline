<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/13 01:46:54
 */
class Version20150413134651 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_type 
            DROP COLUMN is_notifiable
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD is_notifiable BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type 
            ADD CONSTRAINT DF_AEC62693_3296C717 DEFAULT '0' FOR is_notifiable
        ");
    }
}