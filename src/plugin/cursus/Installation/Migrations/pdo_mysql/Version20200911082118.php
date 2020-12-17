<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/09/11 08:21:31
 */
class Version20200911082118 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            CHANGE title course_name VARCHAR(255) NOT NULL,
            DROP with_session_event
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP session_status, 
            CHANGE session_name course_name VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            CHANGE course_name title VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
            ADD with_session_event TINYINT(1) DEFAULT "1" NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD session_status INT NOT NULL, 
            CHANGE course_name session_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
    }
}
