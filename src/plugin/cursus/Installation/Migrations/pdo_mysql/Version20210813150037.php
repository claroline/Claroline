<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/08/13 03:00:39
 */
class Version20210813150037 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session_user 
            ADD managed TINYINT(1) NOT NULL, 
            ADD refused TINYINT(1) NOT NULL
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session_user 
            DROP managed, 
            DROP refused
        ");
    }
}
