<?php

namespace Claroline\CursusBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/03/01 01:04:11
 */
class Version20210301130356 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            ADD price DOUBLE PRECISION DEFAULT NULL, 
            ADD priceDescription LONGTEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            ADD price DOUBLE PRECISION DEFAULT NULL, 
            ADD priceDescription LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course 
            DROP price, 
            DROP priceDescription
        ');
        $this->addSql('
            ALTER TABLE claro_cursusbundle_course_session 
            DROP price, 
            DROP priceDescription
        ');
    }
}
