<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/03 02:43:36
 */
class Version20150303144334 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            DROP INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group (session_id, group_id, group_type)
        ');
        $this->addSql('
            DROP INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group (cursus_id, group_id, group_type)
        ');
        $this->addSql('
            DROP INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user (session_id, user_id, user_type)
        ');
        $this->addSql('
            DROP INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user (cursus_id, user_id, user_type)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_group_unique_course_session_group ON claro_cursusbundle_course_session_group (session_id, group_id)
        ');
        $this->addSql('
            DROP INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_user_unique_course_session_user ON claro_cursusbundle_course_session_user (session_id, user_id)
        ');
        $this->addSql('
            DROP INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group (cursus_id, group_id)
        ');
        $this->addSql('
            DROP INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user
        ');
        $this->addSql('
            CREATE UNIQUE INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user (cursus_id, user_id)
        ');
    }
}
