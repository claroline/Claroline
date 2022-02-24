<?php

namespace Claroline\SchedulerBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/02/28 10:02:22
 */
class Version20220228100220 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            ADD execution_type VARCHAR(255) NOT NULL, 
            ADD execution_interval INT DEFAULT NULL, 
            ADD end_date DATETIME DEFAULT NULL, 
            CHANGE task_type task_action VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_scheduled_task SET execution_type = "once"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            ADD task_type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            DROP task_action, 
            DROP execution_type, 
            DROP execution_interval, 
            DROP end_date
        ');
    }
}
