<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/10/14 08:43:20
 */
class Version20211014084318 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            DROP used_by_quotas, 
            DROP quota_days, 
            CHANGE plainDescription plainDescription LONGTEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course CHANGE plainDescription plainDescription LONGTEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session_user 
            DROP status, 
            DROP remark
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course CHANGE plainDescription plainDescription VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            ADD used_by_quotas TINYINT(1) NOT NULL, 
            ADD quota_days DOUBLE PRECISION DEFAULT '0', 
            CHANGE plainDescription plainDescription VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD status INT NOT NULL, 
            ADD remark LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ");
    }
}
