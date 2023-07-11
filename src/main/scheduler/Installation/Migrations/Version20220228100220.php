<?php

namespace Claroline\SchedulerBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 09:42:32
 */
final class Version20220228100220 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE claro_scheduled_task (
                id INT AUTO_INCREMENT NOT NULL, 
                group_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                task_action VARCHAR(255) NOT NULL, 
                execution_type VARCHAR(255) NOT NULL, 
                scheduled_date DATETIME NOT NULL, 
                execution_date DATETIME DEFAULT NULL, 
                execution_interval INT DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                execution_status VARCHAR(255) DEFAULT NULL, 
                parent_id VARCHAR(255) DEFAULT NULL, 
                task_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
                entity_name VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_9D92A4DDD17F50A6 (uuid), 
                INDEX IDX_9D92A4DDFE54D947 (group_id), 
                INDEX IDX_9D92A4DD82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_scheduled_task_users (
                scheduled_task_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_573E7D5EE97157C2 (scheduled_task_id), 
                INDEX IDX_573E7D5EA76ED395 (user_id), 
                PRIMARY KEY(scheduled_task_id, user_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            DROP FOREIGN KEY FK_9D92A4DDFE54D947
        ');
        $this->addSql('
            ALTER TABLE claro_scheduled_task 
            DROP FOREIGN KEY FK_9D92A4DD82D40A1F
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
            DROP TABLE claro_scheduled_task
        ');
        $this->addSql('
            DROP TABLE claro_scheduled_task_users
        ');
    }
}
