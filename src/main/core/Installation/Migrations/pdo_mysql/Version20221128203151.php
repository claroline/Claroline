<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/11/28 08:32:05
 */
class Version20221128203151 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_group 
            ADD description LONGTEXT DEFAULT NULL, 
            ADD poster VARCHAR(255) DEFAULT NULL, 
            ADD thumbnail VARCHAR(255) DEFAULT NULL, 
            CHANGE is_read_only is_locked TINYINT(1) DEFAULT "0" NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_group 
            CHANGE is_locked is_read_only TINYINT(1) NOT NULL, 
            DROP description, 
            DROP poster, 
            DROP thumbnail
        ');
    }
}
