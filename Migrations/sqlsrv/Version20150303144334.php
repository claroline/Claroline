<?php

namespace Claroline\CursusBundle\Migrations\sqlsrv;

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
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'cursus_group_unique_course_session_group'
            ) 
            ALTER TABLE claro_cursusbundle_course_session_group 
            DROP CONSTRAINT cursus_group_unique_course_session_group ELSE 
            DROP INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group (session_id, group_id, group_type) 
            WHERE session_id IS NOT NULL 
            AND group_id IS NOT NULL 
            AND group_type IS NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'cursus_group_unique_cursus_group'
            ) 
            ALTER TABLE claro_cursusbundle_cursus_group 
            DROP CONSTRAINT cursus_group_unique_cursus_group ELSE 
            DROP INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group (cursus_id, group_id, group_type) 
            WHERE cursus_id IS NOT NULL 
            AND group_id IS NOT NULL 
            AND group_type IS NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'cursus_user_unique_course_session_user'
            ) 
            ALTER TABLE claro_cursusbundle_course_session_user 
            DROP CONSTRAINT cursus_user_unique_course_session_user ELSE 
            DROP INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user (session_id, user_id, user_type) 
            WHERE session_id IS NOT NULL 
            AND user_id IS NOT NULL 
            AND user_type IS NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'cursus_user_unique_cursus_user'
            ) 
            ALTER TABLE claro_cursusbundle_cursus_user 
            DROP CONSTRAINT cursus_user_unique_cursus_user ELSE 
            DROP INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user (cursus_id, user_id, user_type) 
            WHERE cursus_id IS NOT NULL 
            AND user_id IS NOT NULL 
            AND user_type IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'cursus_group_unique_course_session_group'
            ) 
            ALTER TABLE claro_cursusbundle_course_session_group 
            DROP CONSTRAINT cursus_group_unique_course_session_group ELSE 
            DROP INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group (session_id, group_id) 
            WHERE session_id IS NOT NULL 
            AND group_id IS NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'cursus_user_unique_course_session_user'
            ) 
            ALTER TABLE claro_cursusbundle_course_session_user 
            DROP CONSTRAINT cursus_user_unique_course_session_user ELSE 
            DROP INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user (session_id, user_id) 
            WHERE session_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'cursus_group_unique_cursus_group'
            ) 
            ALTER TABLE claro_cursusbundle_cursus_group 
            DROP CONSTRAINT cursus_group_unique_cursus_group ELSE 
            DROP INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group (cursus_id, group_id) 
            WHERE cursus_id IS NOT NULL 
            AND group_id IS NOT NULL
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'cursus_user_unique_cursus_user'
            ) 
            ALTER TABLE claro_cursusbundle_cursus_user 
            DROP CONSTRAINT cursus_user_unique_cursus_user ELSE 
            DROP INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user (cursus_id, user_id) 
            WHERE cursus_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
    }
}