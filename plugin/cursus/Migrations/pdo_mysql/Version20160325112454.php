<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/03/25 11:24:55
 */
class Version20160325112454 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP INDEX UNIQ_C5F56FDEEF2297F5, 
            ADD INDEX IDX_C5F56FDEEF2297F5 (learner_role_id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP INDEX UNIQ_C5F56FDEBEFB2F13, 
            ADD INDEX IDX_C5F56FDEBEFB2F13 (tutor_role_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP INDEX IDX_C5F56FDEEF2297F5, 
            ADD UNIQUE INDEX UNIQ_C5F56FDEEF2297F5 (learner_role_id)
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP INDEX IDX_C5F56FDEBEFB2F13, 
            ADD UNIQUE INDEX UNIQ_C5F56FDEBEFB2F13 (tutor_role_id)
        ');
    }
}
