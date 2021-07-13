<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/06/24 01:10:25
 */
class Version20210624131023 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course 
            ADD session_hours DOUBLE PRECISION DEFAULT '0' NOT NULL, 
            CHANGE session_duration session_days DOUBLE PRECISION DEFAULT '1' NOT NULL
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course 
            DROP session_hours, 
            CHANGE session_days session_duration DOUBLE PRECISION DEFAULT '1' NOT NULL
        ");
    }
}
