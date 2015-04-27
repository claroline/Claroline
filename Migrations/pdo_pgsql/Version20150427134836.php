<?php

namespace Claroline\AgendaBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/27 01:48:39
 */
class Version20150427134836 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            ADD is_all_day BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            ADD is_task BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            ADD is_task_done BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP allday
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP istask
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            ADD allday BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            ADD istask BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP is_all_day
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP is_task
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP is_task_done
        ");
    }
}