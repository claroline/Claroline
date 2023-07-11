<?php

namespace Claroline\AgendaBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 01:36:33
 */
final class Version20210524070509 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_event (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                invitation_template_id INT DEFAULT NULL, 
                planned_object_id INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_B1ADDDB5D17F50A6 (uuid), 
                INDEX IDX_B1ADDDB582D40A1F (workspace_id), 
                INDEX IDX_B1ADDDB5D2D03B8 (invitation_template_id), 
                UNIQUE INDEX UNIQ_B1ADDDB5A669922F (planned_object_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_event_invitation (
                id INT AUTO_INCREMENT NOT NULL, 
                event INT NOT NULL, 
                user_id INT NOT NULL, 
                status VARCHAR(255) NOT NULL, 
                INDEX IDX_19D2F4603BAE0AA7 (event), 
                INDEX IDX_19D2F460A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_task (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                planned_object_id INT NOT NULL, 
                is_task_done TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_3460253ED17F50A6 (uuid), 
                INDEX IDX_3460253E82D40A1F (workspace_id), 
                UNIQUE INDEX UNIQ_3460253EA669922F (planned_object_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB5D2D03B8 FOREIGN KEY (invitation_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB5A669922F FOREIGN KEY (planned_object_id) 
            REFERENCES claro_planned_object (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_event_invitation 
            ADD CONSTRAINT FK_19D2F4603BAE0AA7 FOREIGN KEY (event) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_event_invitation 
            ADD CONSTRAINT FK_19D2F460A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            ADD CONSTRAINT FK_3460253E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            ADD CONSTRAINT FK_3460253EA669922F FOREIGN KEY (planned_object_id) 
            REFERENCES claro_planned_object (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB582D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB5D2D03B8
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB5A669922F
        ');
        $this->addSql('
            ALTER TABLE claro_event_invitation 
            DROP FOREIGN KEY FK_19D2F4603BAE0AA7
        ');
        $this->addSql('
            ALTER TABLE claro_event_invitation 
            DROP FOREIGN KEY FK_19D2F460A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            DROP FOREIGN KEY FK_3460253E82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            DROP FOREIGN KEY FK_3460253EA669922F
        ');
        $this->addSql('
            DROP TABLE claro_event
        ');
        $this->addSql('
            DROP TABLE claro_event_invitation
        ');
        $this->addSql('
            DROP TABLE claro_task
        ');
    }
}
