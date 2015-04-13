<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

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
            ADD COLUMN is_notifiable SMALLINT NOT NULL
        ");
    }
}