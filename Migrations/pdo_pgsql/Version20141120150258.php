<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/20 03:02:59
 */
class Version20141120150258 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            ADD istask BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event ALTER allday 
            SET 
                NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            DROP istask
        ");
        $this->addSql("
            ALTER TABLE claro_event ALTER allday 
            DROP NOT NULL
        ");
    }
}