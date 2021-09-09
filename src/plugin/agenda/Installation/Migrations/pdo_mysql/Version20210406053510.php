<?php

namespace Claroline\AgendaBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/04/06 05:35:12
 */
class Version20210406053510 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB561220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB564D218E
        ');
        $this->addSql('
            DROP INDEX IDX_B1ADDDB564D218E ON claro_event
        ');
        $this->addSql('
            DROP INDEX IDX_B1ADDDB561220EA6 ON claro_event
        ');
        // move data in new table in CoreBundle
        $this->addSql('
            INSERT INTO claro_planned_object (id, location_id, creator_id, event_type, start_date, end_date, color, description, uuid, entity_name, poster, thumbnail, event_class)
                SELECT id, location_id, creator_id, event_type, start_date, end_date, color, description, uuid, entity_name, poster, thumbnail, "Claroline\\\AgendaBundle\\\Entity\\\Event" AS event_class 
                FROM claro_event
                WHERE event_type = "event"
        ');
        $this->addSql('
            INSERT INTO claro_planned_object (id, location_id, creator_id, event_type, start_date, end_date, color, description, uuid, entity_name, poster, thumbnail, event_class)
                SELECT id, location_id, creator_id, event_type, start_date, end_date, color, description, uuid, entity_name, poster, thumbnail, "Claroline\\\AgendaBundle\\\Entity\\\Task" AS event_class
                FROM claro_event
                WHERE event_type = "task"
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD planned_object_id INT NOT NULL, 
            DROP creator_id, 
            DROP location_id, 
            DROP entity_name, 
            DROP start_date, 
            DROP end_date, 
            DROP description, 
            DROP color, 
            DROP thumbnail, 
            DROP event_type, 
            DROP poster
        ');
        // link events with PlannedObject
        $this->addSql('
            UPDATE claro_event SET planned_object_id = id
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB5A669922F FOREIGN KEY (planned_object_id) 
            REFERENCES claro_planned_object (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_B1ADDDB5A669922F ON claro_event (planned_object_id)
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            DROP FOREIGN KEY FK_3460253E71F7E88B
        ');
        $this->addSql('
            DROP INDEX UNIQ_3460253E71F7E88B ON claro_task
        ');
        $this->addSql('
            ALTER TABLE claro_task CHANGE event_id planned_object_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            ADD CONSTRAINT FK_3460253EA669922F FOREIGN KEY (planned_object_id) 
            REFERENCES claro_planned_object (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_3460253EA669922F ON claro_task (planned_object_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_event 
            DROP FOREIGN KEY FK_B1ADDDB5A669922F
        ');
        $this->addSql('
            DROP INDEX UNIQ_B1ADDDB5A669922F ON claro_event
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD workspace_id INT DEFAULT NULL, 
            ADD creator_id INT DEFAULT NULL, 
            ADD location_id INT DEFAULT NULL, 
            ADD entity_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            ADD start_date DATETIME DEFAULT NULL, 
            ADD end_date DATETIME DEFAULT NULL, 
            ADD description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD color VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD thumbnail VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD event_type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            ADD poster VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            DROP planned_object_id
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB561220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB564D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_event 
            ADD CONSTRAINT FK_B1ADDDB582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_B1ADDDB582D40A1F ON claro_event (workspace_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_B1ADDDB564D218E ON claro_event (location_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_B1ADDDB561220EA6 ON claro_event (creator_id)
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            DROP FOREIGN KEY FK_3460253EA669922F
        ');
        $this->addSql('
            DROP INDEX UNIQ_3460253EA669922F ON claro_task
        ');
        $this->addSql('
            ALTER TABLE claro_task CHANGE planned_object_id event_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_task 
            ADD CONSTRAINT FK_3460253E71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_3460253E71F7E88B ON claro_task (event_id)
        ');
    }
}
