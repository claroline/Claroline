<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/06/23 10:02:36
 */
class Version20170623100234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scheduled_task (
                id INT AUTO_INCREMENT NOT NULL, 
                group_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                task_type VARCHAR(255) NOT NULL, 
                task_name VARCHAR(255) DEFAULT NULL, 
                scheduled_date DATETIME NOT NULL, 
                executed TINYINT(1) NOT NULL, 
                execution_date DATETIME DEFAULT NULL, 
                execution_status VARCHAR(255) DEFAULT NULL, 
                task_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                INDEX IDX_9D92A4DDFE54D947 (group_id), 
                INDEX IDX_9D92A4DD82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_scheduled_task_users (
                scheduledtask_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_573E7D5ED87B621C (scheduledtask_id), 
                INDEX IDX_573E7D5EA76ED395 (user_id), 
                PRIMARY KEY(scheduledtask_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_scheduled_task_users 
            DROP FOREIGN KEY FK_573E7D5ED87B621C
        ');
        $this->addSql('
            DROP TABLE claro_scheduled_task
        ');
        $this->addSql('
            DROP TABLE claro_scheduled_task_users
        ');
    }
}
