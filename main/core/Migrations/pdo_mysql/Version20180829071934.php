<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/08/29 07:19:36
 */
class Version20180829071934 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD availableSort LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            ADD availableSort LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP availableSort
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            DROP availableSort
        ');
    }
}
