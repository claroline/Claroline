<?php

namespace Claroline\CursusBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 02:13:23
 */
final class Version20230416150201 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course (
                id INT AUTO_INCREMENT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                learner_role_id INT DEFAULT NULL, 
                tutor_role_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                slug VARCHAR(128) NOT NULL, 
                hideSessions TINYINT(1) NOT NULL, 
                sessionOpening VARCHAR(255) DEFAULT NULL, 
                session_duration DOUBLE PRECISION DEFAULT '1' NOT NULL, 
                plainDescription VARCHAR(255) DEFAULT NULL, 
                public_registration TINYINT(1) NOT NULL, 
                auto_registration TINYINT(1) NOT NULL, 
                public_unregistration TINYINT(1) NOT NULL, 
                registration_validation TINYINT(1) NOT NULL, 
                registration_mail TINYINT(1) NOT NULL, 
                user_validation TINYINT(1) NOT NULL, 
                pending_registrations TINYINT(1) NOT NULL, 
                max_users INT DEFAULT NULL, 
                price DOUBLE PRECISION DEFAULT NULL, 
                priceDescription LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                hidden TINYINT(1) DEFAULT 0 NOT NULL, 
                entity_order INT NOT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                updatedAt DATETIME DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_3359D349989D9B62 (slug), 
                UNIQUE INDEX UNIQ_3359D349D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_3359D34977153098 (code), 
                INDEX IDX_3359D349727ACA70 (parent_id), 
                INDEX IDX_3359D34982D40A1F (workspace_id), 
                INDEX IDX_3359D349EF2297F5 (learner_role_id), 
                INDEX IDX_3359D349BEFB2F13 (tutor_role_id), 
                INDEX IDX_3359D34961220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_organizations (
                course_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_6B367C8591CC992 (course_id), 
                INDEX IDX_6B367C832C8A3DE (organization_id), 
                PRIMARY KEY(course_id, organization_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_panel_facet (
                course_id INT NOT NULL, 
                panel_facet_id INT NOT NULL, 
                INDEX IDX_B108498E591CC992 (course_id), 
                UNIQUE INDEX UNIQ_B108498EF7CB6621 (panel_facet_id), 
                PRIMARY KEY(course_id, panel_facet_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session (
                id INT AUTO_INCREMENT NOT NULL, 
                course_id INT NOT NULL, 
                location_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                learner_role_id INT DEFAULT NULL, 
                tutor_role_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                default_session TINYINT(1) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                event_registration_type INT DEFAULT 0 NOT NULL, 
                plainDescription VARCHAR(255) DEFAULT NULL, 
                public_registration TINYINT(1) NOT NULL, 
                auto_registration TINYINT(1) NOT NULL, 
                public_unregistration TINYINT(1) NOT NULL, 
                registration_validation TINYINT(1) NOT NULL, 
                registration_mail TINYINT(1) NOT NULL, 
                user_validation TINYINT(1) NOT NULL, 
                pending_registrations TINYINT(1) NOT NULL, 
                max_users INT DEFAULT NULL, 
                price DOUBLE PRECISION DEFAULT NULL, 
                priceDescription LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                hidden TINYINT(1) DEFAULT 0 NOT NULL, 
                entity_order INT NOT NULL, 
                createdAt DATETIME DEFAULT NULL, 
                updatedAt DATETIME DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_C5F56FDED17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_C5F56FDE77153098 (code), 
                INDEX IDX_C5F56FDE591CC992 (course_id), 
                INDEX IDX_C5F56FDE64D218E (location_id), 
                INDEX IDX_C5F56FDE82D40A1F (workspace_id), 
                INDEX IDX_C5F56FDEEF2297F5 (learner_role_id), 
                INDEX IDX_C5F56FDEBEFB2F13 (tutor_role_id), 
                INDEX IDX_C5F56FDE61220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_resources (
                resource_id INT NOT NULL, 
                session_id INT NOT NULL, 
                INDEX IDX_4956113E89329D25 (resource_id), 
                UNIQUE INDEX UNIQ_4956113E613FECDF (session_id), 
                PRIMARY KEY(resource_id, session_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event (
                id INT AUTO_INCREMENT NOT NULL, 
                session_id INT NOT NULL, 
                presence_template_id INT DEFAULT NULL, 
                planned_object_id INT NOT NULL, 
                max_users INT DEFAULT NULL, 
                registration_type INT DEFAULT 0 NOT NULL, 
                registration_mail TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_257C3061D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_257C306177153098 (code), 
                INDEX IDX_257C3061613FECDF (session_id), 
                INDEX IDX_257C3061D7F5EEA3 (presence_template_id), 
                UNIQUE INDEX UNIQ_257C3061A669922F (planned_object_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_presence_status (
                id INT AUTO_INCREMENT NOT NULL, 
                event_id INT NOT NULL, 
                user_id INT NOT NULL, 
                presence_status VARCHAR(255) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DFE5E1FED17F50A6 (uuid), 
                INDEX IDX_DFE5E1FE71F7E88B (event_id), 
                INDEX IDX_DFE5E1FEA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_course_user (
                id INT AUTO_INCREMENT NOT NULL, 
                course_id INT NOT NULL, 
                user_id INT NOT NULL, 
                confirmed TINYINT(1) NOT NULL, 
                validated TINYINT(1) NOT NULL, 
                registration_type VARCHAR(255) NOT NULL, 
                registration_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_7246EBFFD17F50A6 (uuid), 
                INDEX IDX_7246EBFF591CC992 (course_id), 
                INDEX IDX_7246EBFFA76ED395 (user_id), 
                UNIQUE INDEX training_session_unique_user (course_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_user_values (
                registration_id INT NOT NULL, 
                value_id INT NOT NULL, 
                INDEX IDX_6882B76B833D8F43 (registration_id), 
                UNIQUE INDEX UNIQ_6882B76BF920BBA2 (value_id), 
                PRIMARY KEY(registration_id, value_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_event_user (
                id INT AUTO_INCREMENT NOT NULL, 
                event_id INT NOT NULL, 
                user_id INT NOT NULL, 
                confirmed TINYINT(1) NOT NULL, 
                validated TINYINT(1) NOT NULL, 
                registration_type VARCHAR(255) NOT NULL, 
                registration_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_31D741DDD17F50A6 (uuid), 
                INDEX IDX_31D741DD71F7E88B (event_id), 
                INDEX IDX_31D741DDA76ED395 (user_id), 
                UNIQUE INDEX training_event_unique_user (event_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_group (
                id INT AUTO_INCREMENT NOT NULL, 
                session_id INT NOT NULL, 
                group_id INT NOT NULL, 
                registration_type VARCHAR(255) NOT NULL, 
                registration_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_F27287A4D17F50A6 (uuid), 
                INDEX IDX_F27287A4613FECDF (session_id), 
                INDEX IDX_F27287A4FE54D947 (group_id), 
                UNIQUE INDEX training_session_unique_group (session_id, group_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_user (
                id INT AUTO_INCREMENT NOT NULL, 
                session_id INT NOT NULL, 
                user_id INT NOT NULL, 
                confirmed TINYINT(1) NOT NULL, 
                validated TINYINT(1) NOT NULL, 
                registration_type VARCHAR(255) NOT NULL, 
                registration_date DATETIME NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_80B4120FD17F50A6 (uuid), 
                INDEX IDX_80B4120F613FECDF (session_id), 
                INDEX IDX_80B4120FA76ED395 (user_id), 
                UNIQUE INDEX training_session_unique_user (session_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_session_user_values (
                registration_id INT NOT NULL, 
                value_id INT NOT NULL, 
                INDEX IDX_E930F53D833D8F43 (registration_id), 
                UNIQUE INDEX UNIQ_E930F53DF920BBA2 (value_id), 
                PRIMARY KEY(registration_id, value_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D34982D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349EF2297F5 FOREIGN KEY (learner_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349BEFB2F13 FOREIGN KEY (tutor_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D34961220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
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
            ALTER TABLE claro_cursusbundle_course_panel_facet 
            ADD CONSTRAINT FK_B108498E591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_panel_facet 
            ADD CONSTRAINT FK_B108498EF7CB6621 FOREIGN KEY (panel_facet_id) 
            REFERENCES claro_panel_facet (id) 
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
            ADD CONSTRAINT FK_C5F56FDE64D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
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
            ADD CONSTRAINT FK_C5F56FDE61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_resources 
            ADD CONSTRAINT FK_4956113E89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_cursusbundle_course_session (id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_resources 
            ADD CONSTRAINT FK_4956113E613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C3061613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C3061D7F5EEA3 FOREIGN KEY (presence_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            ADD CONSTRAINT FK_257C3061A669922F FOREIGN KEY (planned_object_id) 
            REFERENCES claro_planned_object (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD CONSTRAINT FK_DFE5E1FE71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_cursusbundle_session_event (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            ADD CONSTRAINT FK_DFE5E1FEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_cursusbundle_course_user_values 
            ADD CONSTRAINT FK_6882B76B833D8F43 FOREIGN KEY (registration_id) 
            REFERENCES claro_cursusbundle_course_course_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_user_values 
            ADD CONSTRAINT FK_6882B76BF920BBA2 FOREIGN KEY (value_id) 
            REFERENCES claro_field_facet_value (id) 
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
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD CONSTRAINT FK_31D741DD71F7E88B FOREIGN KEY (event_id) 
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
            ALTER TABLE claro_cursusbundle_course_session_group 
            ADD CONSTRAINT FK_F27287A4613FECDF FOREIGN KEY (session_id) 
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
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD CONSTRAINT FK_80B4120F613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD CONSTRAINT FK_80B4120FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_user_values 
            ADD CONSTRAINT FK_E930F53D833D8F43 FOREIGN KEY (registration_id) 
            REFERENCES claro_cursusbundle_course_session_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_user_values 
            ADD CONSTRAINT FK_E930F53DF920BBA2 FOREIGN KEY (value_id) 
            REFERENCES claro_field_facet_value (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D349727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D34982D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D349EF2297F5
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D349BEFB2F13
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP FOREIGN KEY FK_3359D34961220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_organizations 
            DROP FOREIGN KEY FK_6B367C8591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_organizations 
            DROP FOREIGN KEY FK_6B367C832C8A3DE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_panel_facet 
            DROP FOREIGN KEY FK_B108498E591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_panel_facet 
            DROP FOREIGN KEY FK_B108498EF7CB6621
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDE591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDE64D218E
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDE82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDEEF2297F5
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDEBEFB2F13
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDE61220EA6
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_resources 
            DROP FOREIGN KEY FK_4956113E89329D25
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_resources 
            DROP FOREIGN KEY FK_4956113E613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061D7F5EEA3
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event 
            DROP FOREIGN KEY FK_257C3061A669922F
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP FOREIGN KEY FK_DFE5E1FE71F7E88B
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_presence_status 
            DROP FOREIGN KEY FK_DFE5E1FEA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_course_user 
            DROP FOREIGN KEY FK_7246EBFF591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_course_user 
            DROP FOREIGN KEY FK_7246EBFFA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_user_values 
            DROP FOREIGN KEY FK_6882B76B833D8F43
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_user_values 
            DROP FOREIGN KEY FK_6882B76BF920BBA2
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_group 
            DROP FOREIGN KEY FK_9A1E570F71F7E88B
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_group 
            DROP FOREIGN KEY FK_9A1E570FFE54D947
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            DROP FOREIGN KEY FK_31D741DD71F7E88B
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            DROP FOREIGN KEY FK_31D741DDA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            DROP FOREIGN KEY FK_F27287A4613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            DROP FOREIGN KEY FK_F27287A4FE54D947
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            DROP FOREIGN KEY FK_80B4120F613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            DROP FOREIGN KEY FK_80B4120FA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_user_values 
            DROP FOREIGN KEY FK_E930F53D833D8F43
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_user_values 
            DROP FOREIGN KEY FK_E930F53DF920BBA2
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_organizations
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_panel_facet
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_resources
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_presence_status
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_course_user
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_user_values
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_group
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_event_user
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_group
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_user
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_session_user_values
        ');
    }
}
