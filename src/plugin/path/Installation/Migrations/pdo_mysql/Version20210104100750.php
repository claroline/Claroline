<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/01/04 10:08:12
 */
class Version20210104100750 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            ADD show_end_page TINYINT(1) NOT NULL, 
            ADD end_message LONGTEXT DEFAULT NULL,
            DROP structure
        ');

        $this->addSql('
            ALTER TABLE innova_path_progression 
            DROP authorized_access, 
            DROP locked_access, 
            DROP lockedcall_access
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path 
            DROP show_end_page, 
            DROP end_message, 
            ADD structure LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ');

        $this->addSql('
            ALTER TABLE innova_path_progression 
            ADD authorized_access TINYINT(1) NOT NULL, 
            ADD locked_access TINYINT(1) NOT NULL, 
            ADD lockedcall_access TINYINT(1) NOT NULL
        ');
    }
}
