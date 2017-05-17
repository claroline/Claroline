<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/04/04 01:51:04
 */
class Version20170404135103 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD isModel TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_public_file 
            ADD source_type VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_theme CHANGE extending_default extending_default TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_public_file 
            DROP source_type
        ');
        $this->addSql("
            ALTER TABLE claro_theme CHANGE extending_default extending_default TINYINT(1) DEFAULT '0' NOT NULL
        ");
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP isModel
        ');
    }
}
