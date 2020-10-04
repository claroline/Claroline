<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/10/02 09:10:01
 */
class Version20201002090951 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_course_user 
            ADD confirmed TINYINT(1) NOT NULL, 
            ADD validated TINYINT(1) NOT NULL, 
            DROP registration_status
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD confirmed TINYINT(1) NOT NULL, 
            ADD validated TINYINT(1) NOT NULL, 
            DROP registration_status
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD confirmed TINYINT(1) NOT NULL, 
            ADD validated TINYINT(1) NOT NULL, 
            DROP registration_status
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_course_user 
            ADD registration_status VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            DROP confirmed, 
            DROP validated
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD registration_status VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            DROP confirmed, 
            DROP validated
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_session_event_user 
            ADD registration_status VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            DROP confirmed, 
            DROP validated
        ');
    }
}
