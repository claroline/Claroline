<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/02 08:53:07
 */
class Version20181114110857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_model_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                tutor_role_name VARCHAR(255) DEFAULT NULL, 
                learner_role_name VARCHAR(255) DEFAULT NULL, 
                icon VARCHAR(255) DEFAULT NULL, 
                session_duration INT DEFAULT 1 NOT NULL, 
                with_session_event TINYINT(1) DEFAULT '1' NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                public_registration TINYINT(1) NOT NULL, 
                public_unregistration TINYINT(1) NOT NULL, 
                registration_validation TINYINT(1) NOT NULL, 
                user_validation TINYINT(1) NOT NULL, 
                organization_validation TINYINT(1) NOT NULL, 
                max_users INT DEFAULT NULL, 
                display_order INT DEFAULT 500 NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_3359D34977153098 (code), 
                UNIQUE INDEX UNIQ_3359D349D17F50A6 (uuid), 
                INDEX IDX_3359D349EE7F5384 (workspace_model_id), 
                INDEX IDX_3359D34982D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_validators (
                course_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_219067F2591CC992 (course_id), 
                INDEX IDX_219067F2A76ED395 (user_id), 
                PRIMARY KEY(course_id, user_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_organizations (
                course_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_6B367C8591CC992 (course_id), 
                INDEX IDX_6B367C832C8A3DE (organization_id), 
                PRIMARY KEY(course_id, organization_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_session (
                id INT AUTO_INCREMENT NOT NULL, 
                course_id INT NOT NULL, 
                learner_role_id INT DEFAULT NULL, 
                tutor_role_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                session_name VARCHAR(255) NOT NULL, 
                session_status INT NOT NULL, 
                default_session TINYINT(1) NOT NULL, 
                creation_date DATETIME NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                session_type INT NOT NULL, 
                event_registration_type INT DEFAULT 0 NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                code VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                public_registration TINYINT(1) NOT NULL, 
                public_unregistration TINYINT(1) NOT NULL, 
                registration_validation TINYINT(1) NOT NULL, 
                user_validation TINYINT(1) NOT NULL, 
                organization_validation TINYINT(1) NOT NULL, 
                max_users INT DEFAULT NULL, 
                display_order INT DEFAULT 500 NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_C5F56FDE77153098 (code), 
                UNIQUE INDEX UNIQ_C5F56FDED17F50A6 (uuid), 
                INDEX IDX_C5F56FDE591CC992 (course_id), 
                INDEX IDX_C5F56FDEEF2297F5 (learner_role_id), 
                INDEX IDX_C5F56FDEBEFB2F13 (tutor_role_id), 
                INDEX IDX_C5F56FDE82D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_cursus_sessions (
                coursesession_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                INDEX IDX_5256A813AE020D6E (coursesession_id), 
                INDEX IDX_5256A81340AEF4B9 (cursus_id), 
                PRIMARY KEY(coursesession_id, cursus_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_validators (
                coursesession_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_7EE284A7AE020D6E (coursesession_id), 
                INDEX IDX_7EE284A7A76ED395 (user_id), 
                PRIMARY KEY(coursesession_id, user_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus (
                id INT AUTO_INCREMENT NOT NULL, 
                course_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                code VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                blocking TINYINT(1) NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                cursus_order INT NOT NULL, 
                icon VARCHAR(255) DEFAULT NULL, 
                root INT DEFAULT NULL, 
                lvl INT NOT NULL, 
                lft INT NOT NULL, 
                rgt INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_27921C3377153098 (code), 
                UNIQUE INDEX UNIQ_27921C33D17F50A6 (uuid), 
                INDEX IDX_27921C33591CC992 (course_id), 
                INDEX IDX_27921C33727ACA70 (parent_id), 
                INDEX IDX_27921C3382D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_cursusbundle_cursus_organizations (
                cursus_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_3B65A4C840AEF4B9 (cursus_id), 
                INDEX IDX_3B65A4C832C8A3DE (organization_id), 
                PRIMARY KEY(cursus_id, organization_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_cursus_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                registration_date DATETIME NOT NULL, 
                user_type INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_8AA52D8D17F50A6 (uuid), 
                INDEX IDX_8AA52D8A76ED395 (user_id), 
                INDEX IDX_8AA52D840AEF4B9 (cursus_id), 
                UNIQUE INDEX cursus_user_unique_cursus_user (cursus_id, user_id, user_type), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_cursus_group (
                id INT AUTO_INCREMENT NOT NULL, 
                group_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                registration_date DATETIME NOT NULL, 
                group_type INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_EA4DDE93D17F50A6 (uuid), 
                INDEX IDX_EA4DDE93FE54D947 (group_id), 
                INDEX IDX_EA4DDE9340AEF4B9 (cursus_id), 
                UNIQUE INDEX cursus_group_unique_cursus_group (cursus_id, group_id, group_type), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                session_id INT NOT NULL, 
                registration_date DATETIME NOT NULL, 
                user_type INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_80B4120FD17F50A6 (uuid), 
                INDEX IDX_80B4120FA76ED395 (user_id), 
                INDEX IDX_80B4120F613FECDF (session_id), 
                UNIQUE INDEX cursus_user_unique_course_session_user (session_id, user_id, user_type), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_group (
                id INT AUTO_INCREMENT NOT NULL, 
                group_id INT NOT NULL, 
                session_id INT NOT NULL, 
                registration_date DATETIME NOT NULL, 
                group_type INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_F27287A4D17F50A6 (uuid), 
                INDEX IDX_F27287A4FE54D947 (group_id), 
                INDEX IDX_F27287A4613FECDF (session_id), 
                UNIQUE INDEX cursus_group_unique_course_session_group (session_id, group_id, group_type), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event (
                id INT AUTO_INCREMENT NOT NULL, 
                session_id INT NOT NULL, 
                location_id INT DEFAULT NULL, 
                event_set INT DEFAULT NULL, 
                event_name VARCHAR(255) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                start_date DATETIME NOT NULL, 
                end_date DATETIME NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                location_extra LONGTEXT DEFAULT NULL, 
                max_users INT DEFAULT NULL, 
                registration_type INT DEFAULT 0 NOT NULL, 
                event_type INT DEFAULT 0 NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_257C306177153098 (code), 
                UNIQUE INDEX UNIQ_257C3061D17F50A6 (uuid), 
                INDEX IDX_257C3061613FECDF (session_id), 
                INDEX IDX_257C306164D218E (location_id), 
                INDEX IDX_257C3061F7DBE00F (event_set), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_tutors (
                sessionevent_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_18D6F45217678BAC (sessionevent_id), 
                INDEX IDX_18D6F452A76ED395 (user_id), 
                PRIMARY KEY(sessionevent_id, user_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_set (
                id INT AUTO_INCREMENT NOT NULL, 
                session_id INT DEFAULT NULL, 
                set_name VARCHAR(255) NOT NULL, 
                set_limit INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_C400AB6DD17F50A6 (uuid), 
                INDEX IDX_C400AB6D613FECDF (session_id), 
                UNIQUE INDEX event_set_unique_name_session (set_name, session_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                session_event_id INT NOT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                edition_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_21DFDBA8D17F50A6 (uuid), 
                INDEX IDX_21DFDBA8A76ED395 (user_id), 
                INDEX IDX_21DFDBA8FA5B88E3 (session_event_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                session_event_id INT NOT NULL, 
                presence_status_id INT DEFAULT NULL, 
                registration_status INT NOT NULL, 
                registration_date DATETIME DEFAULT NULL, 
                application_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_31D741DDD17F50A6 (uuid), 
                INDEX IDX_31D741DDA76ED395 (user_id), 
                INDEX IDX_31D741DDFA5B88E3 (session_event_id), 
                INDEX IDX_31D741DDD079F0B (presence_status_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_presence_status (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                presence_type INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_registration_queue (
                id INT AUTO_INCREMENT NOT NULL, 
                course_id INT NOT NULL, 
                user_id INT NOT NULL, 
                validator_id INT DEFAULT NULL, 
                organization_admin_id INT DEFAULT NULL, 
                application_date DATETIME NOT NULL, 
                queue_status INT NOT NULL, 
                validation_date DATETIME DEFAULT NULL, 
                user_validation_date DATETIME DEFAULT NULL, 
                validator_validation_date DATETIME DEFAULT NULL, 
                organization_validation_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_E068776ED17F50A6 (uuid), 
                INDEX IDX_E068776E591CC992 (course_id), 
                INDEX IDX_E068776EA76ED395 (user_id), 
                INDEX IDX_E068776EB0644AEC (validator_id), 
                INDEX IDX_E068776E8B3340B2 (organization_admin_id), 
                UNIQUE INDEX course_queue_unique_course_user (course_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_registration_queue (
                id INT AUTO_INCREMENT NOT NULL, 
                session_id INT DEFAULT NULL, 
                user_id INT NOT NULL, 
                validator_id INT DEFAULT NULL, 
                organization_admin_id INT DEFAULT NULL, 
                application_date DATETIME NOT NULL, 
                queue_status INT NOT NULL, 
                validation_date DATETIME DEFAULT NULL, 
                user_validation_date DATETIME DEFAULT NULL, 
                validator_validation_date DATETIME DEFAULT NULL, 
                organization_validation_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_334FC296D17F50A6 (uuid), 
                INDEX IDX_334FC296613FECDF (session_id), 
                INDEX IDX_334FC296A76ED395 (user_id), 
                INDEX IDX_334FC296B0644AEC (validator_id), 
                INDEX IDX_334FC2968B3340B2 (organization_admin_id), 
                UNIQUE INDEX session_queue_unique_session_user (session_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_cursusbundle_courses_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                cursus_id INT DEFAULT NULL, 
                default_mode INT DEFAULT 0 NOT NULL, 
                public_sessions_only TINYINT(1) DEFAULT '0' NOT NULL, 
                extra LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                widgetInstance_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_1724E274AB7B5A55 (widgetInstance_id), 
                INDEX IDX_1724E27440AEF4B9 (cursus_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_cursusbundle_cursus_displayed_word (
                id INT AUTO_INCREMENT NOT NULL, 
                word VARCHAR(255) NOT NULL, 
                displayed_name VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_14E7B098C3F17511 (word), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_document_model (
                id INT AUTO_INCREMENT NOT NULL, 
                name LONGTEXT NOT NULL, 
                content LONGTEXT NOT NULL, 
                document_type INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_A346BB4DD17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349EE7F5384 FOREIGN KEY (workspace_model_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D34982D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_validators 
            ADD CONSTRAINT FK_219067F2591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_validators 
            ADD CONSTRAINT FK_219067F2A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDEEF2297F5 FOREIGN KEY (learner_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDEBEFB2F13 FOREIGN KEY (tutor_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursus_sessions 
            ADD CONSTRAINT FK_5256A813AE020D6E FOREIGN KEY (coursesession_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursus_sessions 
            ADD CONSTRAINT FK_5256A81340AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_validators 
            ADD CONSTRAINT FK_7EE284A7AE020D6E FOREIGN KEY (coursesession_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_validators 
            ADD CONSTRAINT FK_7EE284A7A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            ADD CONSTRAINT FK_27921C33591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            ADD CONSTRAINT FK_27921C33727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            ADD CONSTRAINT FK_27921C3382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_organizations 
            ADD CONSTRAINT FK_3B65A4C840AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_organizations 
            ADD CONSTRAINT FK_3B65A4C832C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_user 
            ADD CONSTRAINT FK_8AA52D8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_user 
            ADD CONSTRAINT FK_8AA52D840AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_group 
            ADD CONSTRAINT FK_EA4DDE93FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_group 
            ADD CONSTRAINT FK_EA4DDE9340AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD CONSTRAINT FK_80B4120FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD CONSTRAINT FK_80B4120F613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            ADD CONSTRAINT FK_F27287A4FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            ADD CONSTRAINT FK_F27287A4613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
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
            ADD CONSTRAINT FK_257C3061F7DBE00F FOREIGN KEY (event_set) 
            REFERENCES claro_cursusbundle_session_event_set (id) 
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
            ALTER TABLE claro_cursusbundle_session_event_set 
            ADD CONSTRAINT FK_C400AB6D613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
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
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DDA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DDFA5B88E3 FOREIGN KEY (session_event_id) 
            REFERENCES claro_cursusbundle_session_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DDD079F0B FOREIGN KEY (presence_status_id) 
            REFERENCES claro_cursusbundle_presence_status (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776E591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776EB0644AEC FOREIGN KEY (validator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            ADD CONSTRAINT FK_E068776E8B3340B2 FOREIGN KEY (organization_admin_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC296613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC296A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC296B0644AEC FOREIGN KEY (validator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC2968B3340B2 FOREIGN KEY (organization_admin_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_courses_widget_config 
            ADD CONSTRAINT FK_1724E274AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_courses_widget_config 
            ADD CONSTRAINT FK_1724E27440AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_validators 
            DROP FOREIGN KEY FK_219067F2591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_organizations 
            DROP FOREIGN KEY FK_6B367C8591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDE591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            DROP FOREIGN KEY FK_27921C33591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_registration_queue 
            DROP FOREIGN KEY FK_E068776E591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursus_sessions 
            DROP FOREIGN KEY FK_5256A813AE020D6E
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_validators 
            DROP FOREIGN KEY FK_7EE284A7AE020D6E
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            DROP FOREIGN KEY FK_80B4120F613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            DROP FOREIGN KEY FK_F27287A4613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_set 
            DROP FOREIGN KEY FK_C400AB6D613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            DROP FOREIGN KEY FK_334FC296613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursus_sessions 
            DROP FOREIGN KEY FK_5256A81340AEF4B9
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            DROP FOREIGN KEY FK_27921C33727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_organizations 
            DROP FOREIGN KEY FK_3B65A4C840AEF4B9
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_user 
            DROP FOREIGN KEY FK_8AA52D840AEF4B9
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_group 
            DROP FOREIGN KEY FK_EA4DDE9340AEF4B9
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_courses_widget_config 
            DROP FOREIGN KEY FK_1724E27440AEF4B9
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_tutors 
            DROP FOREIGN KEY FK_18D6F45217678BAC
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_comment 
            DROP FOREIGN KEY FK_21DFDBA8FA5B88E3
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            DROP FOREIGN KEY FK_31D741DDFA5B88E3
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061F7DBE00F
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            DROP FOREIGN KEY FK_31D741DDD079F0B
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_validators
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_organizations
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session
        ');
        $this->addSql('
            DROP TABLE claro_cursus_sessions
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_validators
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus_organizations
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus_user
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus_group
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_user
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_group
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_tutors
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_set
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_comment
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_user
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_presence_status
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_registration_queue
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_registration_queue
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_courses_widget_config
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus_displayed_word
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_document_model
        ');
    }
}
