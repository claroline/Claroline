<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/02/27 10:36:06
 */
class Version20150227103604 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_model_id INT DEFAULT NULL, 
                code VARCHAR(255) NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                public_registration TINYINT(1) NOT NULL, 
                public_unregistration TINYINT(1) NOT NULL, 
                registration_validation TINYINT(1) NOT NULL, 
                tutor_role_name VARCHAR(255) DEFAULT NULL, 
                learner_role_name VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_3359D34977153098 (code), 
                INDEX IDX_3359D349EE7F5384 (workspace_model_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus (
                id INT AUTO_INCREMENT NOT NULL, 
                course_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                code VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                blocking TINYINT(1) NOT NULL, 
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                cursus_order INT NOT NULL, 
                root INT DEFAULT NULL, 
                lvl INT NOT NULL, 
                lft INT NOT NULL, 
                rgt INT NOT NULL, 
                UNIQUE INDEX UNIQ_27921C3377153098 (code), 
                INDEX IDX_27921C33591CC992 (course_id), 
                INDEX IDX_27921C33727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_group (
                id INT AUTO_INCREMENT NOT NULL, 
                group_id INT NOT NULL, 
                session_id INT NOT NULL, 
                registration_date DATETIME NOT NULL, 
                group_type INT NOT NULL, 
                INDEX IDX_F27287A4FE54D947 (group_id), 
                INDEX IDX_F27287A4613FECDF (session_id), 
                UNIQUE INDEX cursus_group_unique_course_session_group (session_id, group_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_cursus_displayed_word (
                id INT AUTO_INCREMENT NOT NULL, 
                word VARCHAR(255) NOT NULL, 
                displayed_name VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_14E7B098C3F17511 (word), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session (
                id INT AUTO_INCREMENT NOT NULL, 
                course_id INT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                learner_role_id INT DEFAULT NULL, 
                tutor_role_id INT DEFAULT NULL, 
                session_name VARCHAR(255) NOT NULL, 
                session_status INT NOT NULL, 
                default_session TINYINT(1) NOT NULL, 
                creation_date DATETIME NOT NULL, 
                public_registration TINYINT(1) NOT NULL, 
                public_unregistration TINYINT(1) NOT NULL, 
                registration_validation TINYINT(1) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                INDEX IDX_C5F56FDE591CC992 (course_id), 
                INDEX IDX_C5F56FDE82D40A1F (workspace_id), 
                UNIQUE INDEX UNIQ_C5F56FDEEF2297F5 (learner_role_id), 
                UNIQUE INDEX UNIQ_C5F56FDEBEFB2F13 (tutor_role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursus_sessions (
                coursesession_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                INDEX IDX_5256A813AE020D6E (coursesession_id), 
                INDEX IDX_5256A81340AEF4B9 (cursus_id), 
                PRIMARY KEY(coursesession_id, cursus_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_cursus_group (
                id INT AUTO_INCREMENT NOT NULL, 
                group_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                registration_date DATETIME NOT NULL, 
                group_type INT DEFAULT NULL, 
                INDEX IDX_EA4DDE93FE54D947 (group_id), 
                INDEX IDX_EA4DDE9340AEF4B9 (cursus_id), 
                UNIQUE INDEX cursus_group_unique_cursus_group (cursus_id, group_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                session_id INT NOT NULL, 
                registration_date DATETIME NOT NULL, 
                user_type INT NOT NULL, 
                INDEX IDX_80B4120FA76ED395 (user_id), 
                INDEX IDX_80B4120F613FECDF (session_id), 
                UNIQUE INDEX cursus_user_unique_course_session_user (session_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_course_session_registration_queue (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                session_id INT NOT NULL, 
                application_date DATETIME NOT NULL, 
                INDEX IDX_334FC296A76ED395 (user_id), 
                INDEX IDX_334FC296613FECDF (session_id), 
                UNIQUE INDEX session_queue_unique_session_user (session_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_cursusbundle_cursus_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                registration_date DATETIME NOT NULL, 
                user_type INT DEFAULT NULL, 
                INDEX IDX_8AA52D8A76ED395 (user_id), 
                INDEX IDX_8AA52D840AEF4B9 (cursus_id), 
                UNIQUE INDEX cursus_user_unique_cursus_user (cursus_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349EE7F5384 FOREIGN KEY (workspace_model_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE SET NULL
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
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC296A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC296613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
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
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            DROP FOREIGN KEY FK_27921C33591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP FOREIGN KEY FK_C5F56FDE591CC992
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus 
            DROP FOREIGN KEY FK_27921C33727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_cursus_sessions 
            DROP FOREIGN KEY FK_5256A81340AEF4B9
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_group 
            DROP FOREIGN KEY FK_EA4DDE9340AEF4B9
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_cursus_user 
            DROP FOREIGN KEY FK_8AA52D840AEF4B9
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_group 
            DROP FOREIGN KEY FK_F27287A4613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursus_sessions 
            DROP FOREIGN KEY FK_5256A813AE020D6E
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            DROP FOREIGN KEY FK_80B4120F613FECDF
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            DROP FOREIGN KEY FK_334FC296613FECDF
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_group
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus_displayed_word
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session
        ');
        $this->addSql('
            DROP TABLE claro_cursus_sessions
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus_group
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_user
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_registration_queue
        ');
        $this->addSql('
            DROP TABLE claro_cursusbundle_cursus_user
        ');
    }
}
