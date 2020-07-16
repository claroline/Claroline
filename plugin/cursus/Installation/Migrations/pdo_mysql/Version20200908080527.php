<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/09/08 08:05:51
 */
class Version20200908080527 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD poster VARCHAR(255) DEFAULT NULL, 
            ADD thumbnail VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP poster, 
            DROP thumbnail
        ');
    }
}
