<?php

namespace Claroline\AgendaBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:08:03
 */
class Version20190831105131 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_event (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT NOT NULL, 
                title VARCHAR(50) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                is_all_day TINYINT(1) NOT NULL, 
                is_task TINYINT(1) NOT NULL, 
                is_task_done TINYINT(1) NOT NULL, 
                priority VARCHAR(255) DEFAULT NULL, 
                is_editable TINYINT(1) DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B1ADDDB5D17F50A6 (uuid), 
                INDEX IDX_B1ADDDB582D40A1F (workspace_id), 
                INDEX IDX_B1ADDDB5A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_event_invitation (
                id INT AUTO_INCREMENT NOT NULL, 
                event INT NOT NULL, 
                user_id INT NOT NULL, 
                status SMALLINT NOT NULL, 
                title VARCHAR(50) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                INDEX IDX_19D2F4603BAE0AA7 (event), 
                INDEX IDX_19D2F460A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
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
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_event_invitation 
            DROP FOREIGN KEY FK_19D2F4603BAE0AA7
        ');
        $this->addSql('
            DROP TABLE claro_event
        ');
        $this->addSql('
            DROP TABLE claro_event_invitation
        ');
    }
}
