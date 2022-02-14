<?php

namespace Claroline\SchedulerBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/02/15 06:00:00
 */
class Version20220215060000 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        $this->skipIf($this->checkTableExists('claro_scheduled_task', $this->connection), 'Migration already executed.');

        $this->addSql('
            CREATE TABLE claro_scheduled_task_users (
                user_id INT NOT NULL, 
                scheduledtask_id INT NOT NULL, 
                INDEX IDX_573E7D5EA76ED395 (user_id), 
                INDEX IDX_573E7D5ED87B621C (scheduledtask_id), 
                PRIMARY KEY(user_id, scheduledtask_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');

        $this->addSql('
            CREATE TABLE claro_scheduled_task (
                id INT AUTO_INCREMENT NOT NULL, 
                group_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                task_type VARCHAR(255) NOT NULL, 
                task_name VARCHAR(255) DEFAULT NULL, 
                scheduled_date DATETIME NOT NULL, 
                execution_date DATETIME DEFAULT NULL, 
                execution_status VARCHAR(255) DEFAULT NULL, 
                task_data LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json_array)", 
                INDEX IDX_9D92A4DDFE54D947 (group_id), 
                INDEX IDX_9D92A4DD82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD CONSTRAINT FK_573E7D5EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            ADD CONSTRAINT FK_573E7D5ED87B621C FOREIGN KEY (scheduledtask_id) 
            REFERENCES claro_scheduled_task (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            ADD CONSTRAINT FK_9D92A4DDFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            ADD CONSTRAINT FK_9D92A4DD82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP FOREIGN KEY FK_573E7D5EA76ED395
        ');

        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            DROP FOREIGN KEY FK_9D92A4DD82D40A1F
        ');

        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            DROP FOREIGN KEY FK_9D92A4DDFE54D947
        ');

        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP FOREIGN KEY FK_573E7D5ED87B621C
        ');

        $this->addSql('
            DROP TABLE claro_scheduled_task_users
        ');

        $this->addSql('
            DROP TABLE claro_scheduled_task
        ');
    }
}
