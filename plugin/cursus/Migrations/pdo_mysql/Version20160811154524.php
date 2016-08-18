<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/08/11 03:45:25
 */
class Version20160811154524 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_organizations (
                course_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_6B367C8591CC992 (course_id), 
                INDEX IDX_6B367C832C8A3DE (organization_id), 
                PRIMARY KEY(course_id, organization_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_document_model (
                id INT AUTO_INCREMENT NOT NULL, 
                name LONGTEXT NOT NULL, 
                content LONGTEXT NOT NULL, 
                document_type INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event (
                id INT AUTO_INCREMENT NOT NULL, 
                session_id INT NOT NULL, 
                location_id INT DEFAULT NULL, 
                location_resource_id INT DEFAULT NULL, 
                reservation_id INT DEFAULT NULL, 
                event_name VARCHAR(255) NOT NULL, 
                start_date DATETIME NOT NULL, 
                end_date DATETIME NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                location_extra LONGTEXT DEFAULT NULL, 
                INDEX IDX_257C3061613FECDF (session_id), 
                INDEX IDX_257C306164D218E (location_id), 
                INDEX IDX_257C30619FE77A61 (location_resource_id), 
                INDEX IDX_257C3061B83297E7 (reservation_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_tutors (
                sessionevent_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_18D6F45217678BAC (sessionevent_id), 
                INDEX IDX_18D6F452A76ED395 (user_id), 
                PRIMARY KEY(sessionevent_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                session_event_id INT NOT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                INDEX IDX_21DFDBA8A76ED395 (user_id), 
                INDEX IDX_21DFDBA8FA5B88E3 (session_event_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_organizations 
            ADD CONSTRAINT FK_6B367C8591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_organizations 
            ADD CONSTRAINT FK_6B367C832C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C3061613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C306164D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C30619FE77A61 FOREIGN KEY (location_resource_id) 
            REFERENCES formalibre_reservation_resource (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C3061B83297E7 FOREIGN KEY (reservation_id) 
            REFERENCES formalibre_reservation (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_tutors 
            ADD CONSTRAINT FK_18D6F45217678BAC FOREIGN KEY (sessionevent_id) 
            REFERENCES claro_cursusbundle_session_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_tutors 
            ADD CONSTRAINT FK_18D6F452A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_comment 
            ADD CONSTRAINT FK_21DFDBA8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_comment 
            ADD CONSTRAINT FK_21DFDBA8FA5B88E3 FOREIGN KEY (session_event_id) 
            REFERENCES claro_cursusbundle_session_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course 
            ADD session_duration INT DEFAULT 1 NOT NULL, 
            ADD with_session_event TINYINT(1) DEFAULT '1' NOT NULL
        ");
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD description LONGTEXT DEFAULT NULL
        ');
        $this->addSql("
            ALTER TABLE claro_cursusbundle_courses_widget_config 
            ADD default_mode INT DEFAULT 0 NOT NULL, 
            ADD public_sessions_only TINYINT(1) DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_tutors 
            DROP FOREIGN KEY FK_18D6F45217678BAC
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_comment 
            DROP FOREIGN KEY FK_21DFDBA8FA5B88E3
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_organizations
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_document_model
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_tutors
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_comment
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP session_duration, 
            DROP with_session_event
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP description
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_courses_widget_config 
            DROP default_mode, 
            DROP public_sessions_only
        ');
    }
}
