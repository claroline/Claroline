<?php

namespace Claroline\AgendaBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/03/11 07:12:52
 */
class Version20210311071251 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event 
            ADD event_type VARCHAR(255) NOT NULL, 
            CHANGE priority color VARCHAR(255) DEFAULT NULL
        ');

        $this->addSql('
            UPDATE claro_event SET event_type = "task" WHERE is_task = 1 
        ');

        $this->addSql('
            UPDATE claro_event SET event_type = "event" WHERE is_task = 0 
        ');

        $this->addSql('
            INSERT INTO claro_task (uuid, event_id, done, workspace_id)
                SELECT UUID() AS uuid, e.id AS event_id, e.is_task_done AS done, e.workspace_id 
                FROM claro_event AS e
                WHERE event_type = "task"
        ');

        $this->addSql('
            ALTER TABLE claro_event 
            DROP is_task,
            DROP is_task_done
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event 
            ADD is_task TINYINT(1) NOT NULL, 
            ADD is_task_done TINYINT(1) NOT NULL, 
            DROP event_type, 
            CHANGE color priority VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            DROP FOREIGN KEY FK_3460253E71F7E88B
        ');
        $this->addSql('
            DROP INDEX IDX_3460253E71F7E88B ON claro_task
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            ADD priority VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            DROP event_id
        ');
    }
}
