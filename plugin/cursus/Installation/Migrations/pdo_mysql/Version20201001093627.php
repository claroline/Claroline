<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/10/01 09:36:33
 */
class Version20201001093627 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_course_user (
                id INT AUTO_INCREMENT NOT NULL, 
                course_id INT NOT NULL, 
                user_id INT NOT NULL, 
                registration_status VARCHAR(255) NOT NULL, 
                registration_type VARCHAR(255) NOT NULL, 
                registration_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_7246EBFFD17F50A6 (uuid), 
                INDEX IDX_7246EBFF591CC992 (course_id), 
                INDEX IDX_7246EBFFA76ED395 (user_id), 
                UNIQUE INDEX training_session_unique_user (course_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_group (
                id INT AUTO_INCREMENT NOT NULL, 
                event_id INT NOT NULL, 
                group_id INT NOT NULL, 
                registration_type VARCHAR(255) NOT NULL, 
                registration_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_9A1E570FD17F50A6 (uuid), 
                INDEX IDX_9A1E570F71F7E88B (event_id), 
                INDEX IDX_9A1E570FFE54D947 (group_id), 
                UNIQUE INDEX training_event_unique_group (event_id, group_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_course_user 
            ADD CONSTRAINT FK_7246EBFF591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_course_user 
            ADD CONSTRAINT FK_7246EBFFA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_group 
            ADD CONSTRAINT FK_9A1E570F71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_cursusbundle_session_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_group 
            ADD CONSTRAINT FK_9A1E570FFE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP event_type
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            DROP FOREIGN KEY FK_31D741DDD079F0B
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            DROP FOREIGN KEY FK_31D741DDFA5B88E3
        ');
        $this->addSql('
            DROP INDEX IDX_31D741DDD079F0B ON claro_cursusbundle_session_event_user
        ');
        $this->addSql('
            DROP INDEX IDX_31D741DDFA5B88E3 ON claro_cursusbundle_session_event_user
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD registration_type VARCHAR(255) NOT NULL, 
            DROP presence_status_id, 
            DROP application_date, 
            CHANGE registration_status registration_status VARCHAR(255) NOT NULL, 
            CHANGE registration_date registration_date DATETIME NOT NULL, 
            CHANGE session_event_id event_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DD71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_cursusbundle_session_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_31D741DD71F7E88B ON claro_cursusbundle_session_event_user (event_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX training_event_unique_user ON claro_cursusbundle_session_event_user (event_id, user_id)
        ');
        $this->addSql('
            DROP INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            ADD registration_type VARCHAR(255) NOT NULL, 
            DROP group_type
        ');
        $this->addSql('
            CREATE UNIQUE INDEX training_session_unique_group ON claro_cursusbundle_course_session_group (session_id, group_id)
        ');
        $this->addSql('
            DROP INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD registration_status VARCHAR(255) NOT NULL, 
            ADD registration_type VARCHAR(255) NOT NULL, 
            DROP user_type
        ');
        $this->addSql('
            CREATE UNIQUE INDEX training_session_unique_user ON claro_cursusbundle_course_session_user (session_id, user_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_course_user
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_group
        ');
        $this->addSql('
            DROP INDEX training_session_unique_group ON claro_cursusbundle_course_session_group
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            ADD group_type INT NOT NULL, 
            DROP registration_type
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group (session_id, group_id, group_type)
        ');
        $this->addSql('
            DROP INDEX training_session_unique_user ON claro_cursusbundle_course_session_user
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD user_type INT NOT NULL, 
            DROP registration_status, 
            DROP registration_type
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user (session_id, user_id, user_type)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD event_type INT DEFAULT 0 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            DROP FOREIGN KEY FK_31D741DD71F7E88B
        ');
        $this->addSql('
            DROP INDEX IDX_31D741DD71F7E88B ON claro_cursusbundle_session_event_user
        ');
        $this->addSql('
            DROP INDEX training_event_unique_user ON claro_cursusbundle_session_event_user
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD presence_status_id INT DEFAULT NULL, 
            ADD application_date DATETIME DEFAULT NULL, 
            DROP registration_type, 
            CHANGE registration_status registration_status INT NOT NULL, 
            CHANGE registration_date registration_date DATETIME DEFAULT NULL, 
            CHANGE event_id session_event_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DDD079F0B FOREIGN KEY (presence_status_id) 
            REFERENCES claro_cursusbundle_presence_status (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DDFA5B88E3 FOREIGN KEY (session_event_id) 
            REFERENCES claro_cursusbundle_session_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_31D741DDD079F0B ON claro_cursusbundle_session_event_user (presence_status_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_31D741DDFA5B88E3 ON claro_cursusbundle_session_event_user (session_event_id)
        ');
    }
}
