<?php

namespace Claroline\SchedulerBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/02/15 07:19:05
 */
class Version20220215071904 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP FOREIGN KEY FK_573E7D5ED87B621C
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP FOREIGN KEY FK_573E7D5EA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_573E7D5ED87B621C ON claro_scheduled_task_users
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users CHANGE scheduledtask_id scheduled_task_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD CONSTRAINT FK_573E7D5EE97157C2 FOREIGN KEY (scheduled_task_id) 
            REFERENCES claro_scheduled_task (id)
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD CONSTRAINT FK_573E7D5EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_573E7D5EE97157C2 ON claro_scheduled_task_users (scheduled_task_id)
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD PRIMARY KEY (scheduled_task_id, user_id)
        ');

        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            CHANGE task_name entity_name VARCHAR(255) NOT NULL, 
            ADD uuid VARCHAR(36) NOT NULL, 
            CHANGE task_data task_data LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            UPDATE claro_scheduled_task SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_9D92A4DDD17F50A6 ON claro_scheduled_task (uuid)
        ');

        $this->addSql('
            UPDATE claro_scheduled_task SET execution_status = "pending" WHERE execution_date IS NULL
        ');

        $this->addSql('
            UPDATE claro_scheduled_task SET execution_status = "success" WHERE execution_date IS NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_9D92A4DDD17F50A6 ON claro_scheduled_task
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            CHANGE entity_name task_name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            DROP uuid, 
            CHANGE task_data task_data LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');

        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP FOREIGN KEY FK_573E7D5EE97157C2
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP FOREIGN KEY FK_573E7D5EA76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_573E7D5EE97157C2 ON claro_scheduled_task_users
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP PRIMARY KEY
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users CHANGE scheduled_task_id scheduledtask_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD CONSTRAINT FK_573E7D5ED87B621C FOREIGN KEY (scheduledtask_id) 
            REFERENCES claro_scheduled_task (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD CONSTRAINT FK_573E7D5EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_573E7D5ED87B621C ON claro_scheduled_task_users (scheduledtask_id)
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD PRIMARY KEY (user_id, scheduledtask_id)
        ');
    }
}
