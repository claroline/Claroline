<?php

namespace Claroline\AgendaBundle\Migrations\pdo_ibm;

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
            ADD COLUMN is_all_day SMALLINT NOT NULL 
            ADD COLUMN is_task SMALLINT NOT NULL 
            ADD COLUMN is_task_done SMALLINT NOT NULL 
            DROP COLUMN allday 
            DROP COLUMN istask
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_event 
            ADD COLUMN allday SMALLINT NOT NULL 
            ADD COLUMN istask SMALLINT NOT NULL 
            DROP COLUMN is_all_day 
            DROP COLUMN is_task 
            DROP COLUMN is_task_done
        ");
    }
}