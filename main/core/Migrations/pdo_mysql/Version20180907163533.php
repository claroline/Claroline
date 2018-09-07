<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/09/07 04:35:35
 */
class Version20180907163533 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_config CHANGE centerTitle centerTitle TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container_config
            ADD alignName VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_config CHANGE centerTitle centerTitle TINYINT(1) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container_config
            DROP alignName
        ');
    }
}
