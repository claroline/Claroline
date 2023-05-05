<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/04/06 07:38:18
 */
class Version20210406073812 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C306164D218E
        ');
        $this->addSql('
            DROP INDEX IDX_257C306164D218E ON claro_cursusbundle_session_event
        ');
        // move data in new table in CoreBundle
        $this->addSql('
            INSERT INTO claro_planned_object (location_id, creator_id, event_type, start_date, end_date, description, uuid, entity_name, poster, thumbnail, event_class)
                SELECT e.location_id, s.creator_id, "training_event" as event_type, e.start_date, e.end_date, e.description, e.uuid, e.event_name, e.poster, e.thumbnail, "Claroline\\CursusBundle\\Entity\\Event" AS event_class 
                FROM claro_cursusbundle_session_event AS e
                LEFT JOIN claro_cursusbundle_course_session AS s ON (e.session_id = s.id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD planned_object_id INT NOT NULL, 
            DROP location_id, 
            DROP event_name, 
            DROP start_date, 
            DROP end_date, 
            DROP description, 
            DROP poster, 
            DROP thumbnail
        ');
        // link training events with PlannedObject
        $this->addSql('
            UPDATE claro_cursusbundle_session_event AS e
            LEFT JOIN claro_planned_object AS po ON (e.uuid = po.uuid)
            SET e.planned_object_id = po.id
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C3061A669922F FOREIGN KEY (planned_object_id) 
            REFERENCES claro_planned_object (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_257C3061A669922F ON claro_cursusbundle_session_event (planned_object_id)
        ');

        // initializes planning for all Sessions
        $this->addSql('
            INSERT INTO claro_planning (uuid, objectId, objectClass)
                SELECT UUID() AS uuid, s.uuid, "Claroline\\CursusBundle\\Entity\\Session" AS objectClass
                FROM claro_cursusbundle_course_session AS s 
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061A669922F
        ');
        $this->addSql('
            DROP INDEX UNIQ_257C3061A669922F ON claro_cursusbundle_session_event
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD location_id INT DEFAULT NULL, 
            ADD event_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            ADD start_date DATETIME NOT NULL, 
            ADD end_date DATETIME NOT NULL, 
            ADD description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD poster VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD thumbnail VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            DROP planned_object_id
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C306164D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_257C306164D218E ON claro_cursusbundle_session_event (location_id)
        ');
    }
}
