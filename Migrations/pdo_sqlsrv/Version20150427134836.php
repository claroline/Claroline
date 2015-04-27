<?php

namespace Claroline\AgendaBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/27 01:48:40
 */
class Version20150427134836 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            ADD is_all_day BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            ADD is_task BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            ADD is_task_done BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP COLUMN allday
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP COLUMN istask
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            ADD allday BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            ADD istask BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP COLUMN is_all_day
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP COLUMN is_task
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP COLUMN is_task_done
        ");
    }
}