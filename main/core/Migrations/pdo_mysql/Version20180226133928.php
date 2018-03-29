<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/02/26 01:39:37
 */
class Version20180226133928 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD PRIMARY KEY (user_id, scheduledtask_id)
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD poster VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP poster
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD PRIMARY KEY (scheduledtask_id, user_id)
        ');
    }
}
