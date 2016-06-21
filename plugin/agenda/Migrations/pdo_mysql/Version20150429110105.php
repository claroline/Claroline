<?php

namespace Claroline\AgendaBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/29 11:01:05
 */
class Version20150429110105 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_event (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT NOT NULL, 
                title VARCHAR(50) NOT NULL, 
                start_date INT DEFAULT NULL, 
                end_date INT DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                allday TINYINT(1) NOT NULL, 
                istask TINYINT(1) NOT NULL, 
                priority VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_B1ADDDB582D40A1F (workspace_id), 
                INDEX IDX_B1ADDDB5A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_event_event_category (
                event_id INT NOT NULL, 
                eventcategory_id INT NOT NULL, 
                INDEX IDX_858F0D4C71F7E88B (event_id), 
                INDEX IDX_858F0D4C29E3B4B5 (eventcategory_id), 
                PRIMARY KEY(event_id, eventcategory_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_event_category (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_408DC8C05E237E06 (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            ALTER TABLE claro_event_event_category 
            ADD CONSTRAINT FK_858F0D4C71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_event_event_category 
            ADD CONSTRAINT FK_858F0D4C29E3B4B5 FOREIGN KEY (eventcategory_id) 
            REFERENCES claro_event_category (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_event_event_category 
            DROP FOREIGN KEY FK_858F0D4C71F7E88B
        ');
        $this->addSql('
            ALTER TABLE claro_event_event_category 
            DROP FOREIGN KEY FK_858F0D4C29E3B4B5
        ');
        $this->addSql('
            DROP TABLE claro_event
        ');
        $this->addSql('
            DROP TABLE claro_event_event_category
        ');
        $this->addSql('
            DROP TABLE claro_event_category
        ');
    }
}
