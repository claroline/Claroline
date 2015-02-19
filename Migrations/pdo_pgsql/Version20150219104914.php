<?php

namespace Claroline\CursusBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/19 10:49:15
 */
class Version20150219104914 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course (
                id SERIAL NOT NULL, 
                workspace_model_id INT DEFAULT NULL, 
                code VARCHAR(255) NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                description TEXT DEFAULT NULL, 
                public_registration BOOLEAN NOT NULL, 
                public_unregistration BOOLEAN NOT NULL, 
                registration_validation BOOLEAN NOT NULL, 
                manager_role_prefix VARCHAR(255) DEFAULT NULL, 
                user_role_prefix VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3359D34977153098 ON claro_cursusbundle_course (code)
        ");
        $this->addSql("
            CREATE INDEX IDX_3359D349EE7F5384 ON claro_cursusbundle_course (workspace_model_id)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus (
                id SERIAL NOT NULL, 
                course_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                code VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                description TEXT DEFAULT NULL, 
                blocking BOOLEAN NOT NULL, 
                details TEXT DEFAULT NULL, 
                cursus_order INT NOT NULL, 
                root INT DEFAULT NULL, 
                lvl INT NOT NULL, 
                lft INT NOT NULL, 
                rgt INT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_27921C3377153098 ON claro_cursusbundle_cursus (code)
        ");
        $this->addSql("
            CREATE INDEX IDX_27921C33591CC992 ON claro_cursusbundle_cursus (course_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_27921C33727ACA70 ON claro_cursusbundle_cursus (parent_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_cursusbundle_cursus.details IS '(DC2Type:json_array)'
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_group (
                id SERIAL NOT NULL, 
                group_id INT NOT NULL, 
                course_id INT NOT NULL, 
                registration_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                group_type INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_91D2ED95FE54D947 ON claro_cursusbundle_course_group (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_91D2ED95591CC992 ON claro_cursusbundle_course_group (course_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_course_group ON claro_cursusbundle_course_group (course_id, group_id)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_user (
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                course_id INT NOT NULL, 
                registration_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                user_type INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_26B2FA12A76ED395 ON claro_cursusbundle_course_user (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_26B2FA12591CC992 ON claro_cursusbundle_course_user (course_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_course_user ON claro_cursusbundle_course_user (course_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_displayed_word (
                id SERIAL NOT NULL, 
                word VARCHAR(255) NOT NULL, 
                displayed_name VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_14E7B098C3F17511 ON claro_cursusbundle_cursus_displayed_word (word)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_session (
                id SERIAL NOT NULL, 
                course_id INT NOT NULL, 
                workspace_id INT NOT NULL, 
                user_role_id INT DEFAULT NULL, 
                manager_role_id INT DEFAULT NULL, 
                cursus_id INT DEFAULT NULL, 
                session_status INT NOT NULL, 
                default_session BOOLEAN NOT NULL, 
                creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C5F56FDE591CC992 ON claro_cursusbundle_course_session (course_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C5F56FDE82D40A1F ON claro_cursusbundle_course_session (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C5F56FDE8E0E3CA6 ON claro_cursusbundle_course_session (user_role_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C5F56FDE68CE17BA ON claro_cursusbundle_course_session (manager_role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C5F56FDE40AEF4B9 ON claro_cursusbundle_course_session (cursus_id)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_group (
                id SERIAL NOT NULL, 
                group_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                registration_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                group_type INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_EA4DDE93FE54D947 ON claro_cursusbundle_cursus_group (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EA4DDE9340AEF4B9 ON claro_cursusbundle_cursus_group (cursus_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group (cursus_id, group_id)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_session_registration_queue (
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                session_id INT NOT NULL, 
                application_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_334FC296A76ED395 ON claro_cursusbundle_course_session_registration_queue (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_334FC296613FECDF ON claro_cursusbundle_course_session_registration_queue (session_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX session_queue_unique_session_user ON claro_cursusbundle_course_session_registration_queue (session_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_user (
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                registration_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                user_type INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8AA52D8A76ED395 ON claro_cursusbundle_cursus_user (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8AA52D840AEF4B9 ON claro_cursusbundle_cursus_user (cursus_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user (cursus_id, user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course 
            ADD CONSTRAINT FK_3359D349EE7F5384 FOREIGN KEY (workspace_model_id) 
            REFERENCES claro_workspace_model (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            ADD CONSTRAINT FK_27921C33591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            ADD CONSTRAINT FK_27921C33727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_group 
            ADD CONSTRAINT FK_91D2ED95FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_group 
            ADD CONSTRAINT FK_91D2ED95591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_user 
            ADD CONSTRAINT FK_26B2FA12A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_user 
            ADD CONSTRAINT FK_26B2FA12591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE8E0E3CA6 FOREIGN KEY (user_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE68CE17BA FOREIGN KEY (manager_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            ADD CONSTRAINT FK_C5F56FDE40AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_group 
            ADD CONSTRAINT FK_EA4DDE93FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_group 
            ADD CONSTRAINT FK_EA4DDE9340AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC296A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            ADD CONSTRAINT FK_334FC296613FECDF FOREIGN KEY (session_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_user 
            ADD CONSTRAINT FK_8AA52D8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_user 
            ADD CONSTRAINT FK_8AA52D840AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            DROP CONSTRAINT FK_27921C33591CC992
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_group 
            DROP CONSTRAINT FK_91D2ED95591CC992
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_user 
            DROP CONSTRAINT FK_26B2FA12591CC992
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            DROP CONSTRAINT FK_C5F56FDE591CC992
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            DROP CONSTRAINT FK_27921C33727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            DROP CONSTRAINT FK_C5F56FDE40AEF4B9
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_group 
            DROP CONSTRAINT FK_EA4DDE9340AEF4B9
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_user 
            DROP CONSTRAINT FK_8AA52D840AEF4B9
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session_registration_queue 
            DROP CONSTRAINT FK_334FC296613FECDF
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_group
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_user
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_displayed_word
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_session
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_session_registration_queue
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_user
        ");
    }
}