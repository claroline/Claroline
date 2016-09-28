<?php

namespace Claroline\CursusBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/09/23 10:32:08
 */
class Version20160923103206 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD display_order INT DEFAULT 500 NOT NULL
        ');
        $this->addSql("
            ALTER TABLE claro_cursusbundle_course_session 
            ADD display_order INT DEFAULT 500 NOT NULL, 
            ADD details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP display_order
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP display_order, 
            DROP details
        ');
    }
}
