<?php

namespace Claroline\CursusBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/03 02:43:36
 */
class Version20150303144334 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX cursus_group_unique_course_session_group
        ");
        $this->addSql("
            DROP INDEX IDX_F27287A4FE54D947
        ");
        $this->addSql("
            DROP INDEX IDX_F27287A4613FECDF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_course_session_group AS 
            SELECT id, 
            session_id, 
            group_id, 
            registration_date, 
            group_type 
            FROM claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_session_group (
                id INTEGER NOT NULL, 
                session_id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                group_type INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F27287A4613FECDF FOREIGN KEY (session_id) 
                REFERENCES claro_cursusbundle_course_session (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F27287A4FE54D947 FOREIGN KEY (group_id) 
                REFERENCES claro_group (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_course_session_group (
                id, session_id, group_id, registration_date, 
                group_type
            ) 
            SELECT id, 
            session_id, 
            group_id, 
            registration_date, 
            group_type 
            FROM __temp__claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group (session_id, group_id, group_type)
        ");
        $this->addSql("
            CREATE INDEX IDX_F27287A4FE54D947 ON claro_cursusbundle_course_session_group (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F27287A4613FECDF ON claro_cursusbundle_course_session_group (session_id)
        ");
        $this->addSql("
            DROP INDEX cursus_group_unique_cursus_group
        ");
        $this->addSql("
            DROP INDEX IDX_EA4DDE93FE54D947
        ");
        $this->addSql("
            DROP INDEX IDX_EA4DDE9340AEF4B9
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_cursus_group AS 
            SELECT id, 
            cursus_id, 
            group_id, 
            registration_date, 
            group_type 
            FROM claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_group (
                id INTEGER NOT NULL, 
                cursus_id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                group_type INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EA4DDE9340AEF4B9 FOREIGN KEY (cursus_id) 
                REFERENCES claro_cursusbundle_cursus (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EA4DDE93FE54D947 FOREIGN KEY (group_id) 
                REFERENCES claro_group (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_cursus_group (
                id, cursus_id, group_id, registration_date, 
                group_type
            ) 
            SELECT id, 
            cursus_id, 
            group_id, 
            registration_date, 
            group_type 
            FROM __temp__claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group (cursus_id, group_id, group_type)
        ");
        $this->addSql("
            CREATE INDEX IDX_EA4DDE93FE54D947 ON claro_cursusbundle_cursus_group (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EA4DDE9340AEF4B9 ON claro_cursusbundle_cursus_group (cursus_id)
        ");
        $this->addSql("
            DROP INDEX cursus_user_unique_course_session_user
        ");
        $this->addSql("
            DROP INDEX IDX_80B4120FA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_80B4120F613FECDF
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_course_session_user AS 
            SELECT id, 
            session_id, 
            user_id, 
            registration_date, 
            user_type 
            FROM claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_session_user (
                id INTEGER NOT NULL, 
                session_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                user_type INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_80B4120F613FECDF FOREIGN KEY (session_id) 
                REFERENCES claro_cursusbundle_course_session (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_80B4120FA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_course_session_user (
                id, session_id, user_id, registration_date, 
                user_type
            ) 
            SELECT id, 
            session_id, 
            user_id, 
            registration_date, 
            user_type 
            FROM __temp__claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user (session_id, user_id, user_type)
        ");
        $this->addSql("
            CREATE INDEX IDX_80B4120FA76ED395 ON claro_cursusbundle_course_session_user (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_80B4120F613FECDF ON claro_cursusbundle_course_session_user (session_id)
        ");
        $this->addSql("
            DROP INDEX cursus_user_unique_cursus_user
        ");
        $this->addSql("
            DROP INDEX IDX_8AA52D8A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_8AA52D840AEF4B9
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_cursus_user AS 
            SELECT id, 
            cursus_id, 
            user_id, 
            registration_date, 
            user_type 
            FROM claro_cursusbundle_cursus_user
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_user
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_user (
                id INTEGER NOT NULL, 
                cursus_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                user_type INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_8AA52D840AEF4B9 FOREIGN KEY (cursus_id) 
                REFERENCES claro_cursusbundle_cursus (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_8AA52D8A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_cursus_user (
                id, cursus_id, user_id, registration_date, 
                user_type
            ) 
            SELECT id, 
            cursus_id, 
            user_id, 
            registration_date, 
            user_type 
            FROM __temp__claro_cursusbundle_cursus_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_cursus_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user (cursus_id, user_id, user_type)
        ");
        $this->addSql("
            CREATE INDEX IDX_8AA52D8A76ED395 ON claro_cursusbundle_cursus_user (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8AA52D840AEF4B9 ON claro_cursusbundle_cursus_user (cursus_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_F27287A4FE54D947
        ");
        $this->addSql("
            DROP INDEX IDX_F27287A4613FECDF
        ");
        $this->addSql("
            DROP INDEX cursus_group_unique_course_session_group
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_course_session_group AS 
            SELECT id, 
            group_id, 
            session_id, 
            registration_date, 
            group_type 
            FROM claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_session_group (
                id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                session_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                group_type INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F27287A4FE54D947 FOREIGN KEY (group_id) 
                REFERENCES claro_group (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F27287A4613FECDF FOREIGN KEY (session_id) 
                REFERENCES claro_cursusbundle_course_session (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_course_session_group (
                id, group_id, session_id, registration_date, 
                group_type
            ) 
            SELECT id, 
            group_id, 
            session_id, 
            registration_date, 
            group_type 
            FROM __temp__claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            CREATE INDEX IDX_F27287A4FE54D947 ON claro_cursusbundle_course_session_group (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F27287A4613FECDF ON claro_cursusbundle_course_session_group (session_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group (session_id, group_id)
        ");
        $this->addSql("
            DROP INDEX IDX_80B4120FA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_80B4120F613FECDF
        ");
        $this->addSql("
            DROP INDEX cursus_user_unique_course_session_user
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_course_session_user AS 
            SELECT id, 
            user_id, 
            session_id, 
            registration_date, 
            user_type 
            FROM claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course_session_user (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                session_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                user_type INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_80B4120FA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_80B4120F613FECDF FOREIGN KEY (session_id) 
                REFERENCES claro_cursusbundle_course_session (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_course_session_user (
                id, user_id, session_id, registration_date, 
                user_type
            ) 
            SELECT id, 
            user_id, 
            session_id, 
            registration_date, 
            user_type 
            FROM __temp__claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            CREATE INDEX IDX_80B4120FA76ED395 ON claro_cursusbundle_course_session_user (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_80B4120F613FECDF ON claro_cursusbundle_course_session_user (session_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user (session_id, user_id)
        ");
        $this->addSql("
            DROP INDEX IDX_EA4DDE93FE54D947
        ");
        $this->addSql("
            DROP INDEX IDX_EA4DDE9340AEF4B9
        ");
        $this->addSql("
            DROP INDEX cursus_group_unique_cursus_group
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_cursus_group AS 
            SELECT id, 
            group_id, 
            cursus_id, 
            registration_date, 
            group_type 
            FROM claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_group (
                id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                cursus_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                group_type INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EA4DDE93FE54D947 FOREIGN KEY (group_id) 
                REFERENCES claro_group (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EA4DDE9340AEF4B9 FOREIGN KEY (cursus_id) 
                REFERENCES claro_cursusbundle_cursus (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_cursus_group (
                id, group_id, cursus_id, registration_date, 
                group_type
            ) 
            SELECT id, 
            group_id, 
            cursus_id, 
            registration_date, 
            group_type 
            FROM __temp__claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_cursus_group
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
            DROP INDEX IDX_8AA52D8A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_8AA52D840AEF4B9
        ");
        $this->addSql("
            DROP INDEX cursus_user_unique_cursus_user
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_cursusbundle_cursus_user AS 
            SELECT id, 
            user_id, 
            cursus_id, 
            registration_date, 
            user_type 
            FROM claro_cursusbundle_cursus_user
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_user
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_user (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                cursus_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                user_type INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_8AA52D8A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_8AA52D840AEF4B9 FOREIGN KEY (cursus_id) 
                REFERENCES claro_cursusbundle_cursus (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_cursusbundle_cursus_user (
                id, user_id, cursus_id, registration_date, 
                user_type
            ) 
            SELECT id, 
            user_id, 
            cursus_id, 
            registration_date, 
            user_type 
            FROM __temp__claro_cursusbundle_cursus_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_cursusbundle_cursus_user
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
    }
}