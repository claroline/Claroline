<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/10/05 10:00:00
 */
class Version20211005100000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_cursusbundle_course_session_registration_queue
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
