<?php

namespace Claroline\AgendaBundle\Migrations\pdo_oci;

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
            ADD (
                is_all_day NUMBER(1) NOT NULL, 
                is_task NUMBER(1) NOT NULL, 
                is_task_done NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP (allday, istask)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            ADD (
                allday NUMBER(1) NOT NULL, 
                istask NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_event 
            DROP (
                is_all_day, is_task, is_task_done
            )
        ");
    }
}