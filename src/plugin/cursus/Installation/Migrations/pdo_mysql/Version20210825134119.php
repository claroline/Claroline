<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/08/25 01:41:21
 */
class Version20210825134119 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD auto_registration TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD auto_registration TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP auto_registration
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP auto_registration
        ');
    }
}
