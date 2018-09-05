<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/08/30 03:57:12
 */
class Version20180830155711 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_config CHANGE centerTitle centerTitle TINYINT(1)
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles
            ADD CONSTRAINT FK_B81359F339727CCF FOREIGN KEY (hometabconfig_id)
            REFERENCES claro_home_tab_config (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container_config
            ADD is_visible TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_config CHANGE centerTitle centerTitle TINYINT(1) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles
            DROP FOREIGN KEY FK_B81359F339727CCF
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container_config
            DROP is_visible
        ');
    }
}
