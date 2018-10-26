<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/10/26 02:34:24
 */
class Version20181026143420 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD actions TINYINT(1) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_directory SET actions = 1
        ');
        $this->addSql('
            UPDATE claro_directory SET card = "[\"icon\", \"flags\", \"subtitle\", \"description\", \"footer\"]"
        ');

        $this->addSql('
            ALTER TABLE claro_widget_list 
            ADD actions TINYINT(1) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_widget_list SET actions = 1
        ');
        $this->addSql('
            UPDATE claro_widget_list SET card = "[\"icon\", \"flags\", \"subtitle\", \"description\", \"footer\"]"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP actions
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            DROP actions
        ');
    }
}
