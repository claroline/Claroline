<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/08/16 04:29:50
 */
class Version20210816162948 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            ADD quota_days DOUBLE PRECISION DEFAULT '0', 
            ADD quota_hours DOUBLE PRECISION DEFAULT '0'
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP quota_days, 
            DROP quota_hours
        ');
    }
}
