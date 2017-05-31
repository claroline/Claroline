<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/05/01 04:22:15
 */
class Version20170501162214 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_node
            ADD fullscreen TINYINT(1) NOT NULL,
            ADD closable TINYINT(1) NOT NULL,
            ADD closeTarget INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action
            ADD `group_name` VARCHAR(255) DEFAULT NULL,
            ADD icon VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_node
            DROP fullscreen,
            DROP closable,
            DROP closeTarget
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action
            DROP `group_name`,
            DROP icon
        ');
    }
}
