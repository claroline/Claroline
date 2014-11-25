<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/20 03:03:00
 */
class Version20141120150258 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            ADD istask BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event ALTER COLUMN allday BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            DROP COLUMN istask
        ");
        $this->addSql("
            ALTER TABLE claro_event ALTER COLUMN allday BIT
        ");
    }
}