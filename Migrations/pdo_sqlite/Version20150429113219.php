<?php

namespace Claroline\CursusBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/29 11:32:21
 */
class Version20150429113219 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_registration_queue (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                course_id INTEGER NOT NULL, 
                application_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_E068776EA76ED395 ON claro_cursusbundle_course_registration_queue (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E068776E591CC992 ON claro_cursusbundle_course_registration_queue (course_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX course_queue_unique_course_user ON claro_cursusbundle_course_registration_queue (course_id, user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course 
            ADD COLUMN icon VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            ADD COLUMN icon VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_registration_queue
        ");
        $this->addSql("
            DROP INDEX UNIQ_3359D34977153098
        ");
        $this->addSql("
            DROP INDEX IDX_3359D349EE7F5384
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_course AS 
            SELECT id, 
            workspace_model_id, 
            code, 
            title, 
            description, 
            public_registration, 
            public_unregistration, 
            registration_validation, 
            tutor_role_name, 
            learner_role_name 
            FROM claro_cursusbundle_course
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course (
                id INTEGER NOT NULL, 
                workspace_model_id INTEGER DEFAULT NULL, 
                code VARCHAR(255) NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                public_registration BOOLEAN NOT NULL, 
                public_unregistration BOOLEAN NOT NULL, 
                registration_validation BOOLEAN NOT NULL, 
                tutor_role_name VARCHAR(255) DEFAULT NULL, 
                learner_role_name VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3359D349EE7F5384 FOREIGN KEY (workspace_model_id) 
                REFERENCES claro_workspace_model (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_course (
                id, workspace_model_id, code, title, 
                description, public_registration, 
                public_unregistration, registration_validation, 
                tutor_role_name, learner_role_name
            ) 
            SELECT id, 
            workspace_model_id, 
            code, 
            title, 
            description, 
            public_registration, 
            public_unregistration, 
            registration_validation, 
            tutor_role_name, 
            learner_role_name 
            FROM __temp__claro_cursusbundle_course
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_course
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3359D34977153098 ON claro_cursusbundle_course (code)
        ");
        $this->addSql("
            CREATE INDEX IDX_3359D349EE7F5384 ON claro_cursusbundle_course (workspace_model_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_27921C3377153098
        ");
        $this->addSql("
            DROP INDEX IDX_27921C33591CC992
        ");
        $this->addSql("
            DROP INDEX IDX_27921C33727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_cursus AS 
            SELECT id, 
            course_id, 
            parent_id, 
            code, 
            title, 
            description, 
            blocking, 
            details, 
            cursus_order, 
            root, 
            lvl, 
            lft, 
            rgt 
            FROM claro_cursusbundle_cursus
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus (
                id INTEGER NOT NULL, 
                course_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                code VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                blocking BOOLEAN NOT NULL, 
                details CLOB DEFAULT NULL, 
                cursus_order INTEGER NOT NULL, 
                root INTEGER DEFAULT NULL, 
                lvl INTEGER NOT NULL, 
                lft INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_27921C33591CC992 FOREIGN KEY (course_id) 
                REFERENCES claro_cursusbundle_course (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_27921C33727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_cursusbundle_cursus (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_cursus (
                id, course_id, parent_id, code, title, 
                description, blocking, details, cursus_order, 
                root, lvl, lft, rgt
            ) 
            SELECT id, 
            course_id, 
            parent_id, 
            code, 
            title, 
            description, 
            blocking, 
            details, 
            cursus_order, 
            root, 
            lvl, 
            lft, 
            rgt 
            FROM __temp__claro_cursusbundle_cursus
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_cursus
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
    }
}