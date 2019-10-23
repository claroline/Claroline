<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/10/22 10:46:12
 */
class Version20191022104610 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_config
            DROP is_visible
        ');

        $this->addSql('
            ALTER TABLE claro_home_tab_config
            CHANGE is_locked is_visible TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_config
            CHANGE is_visible is_locked TINYINT(1) NOT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_home_tab_config 
            ADD is_visible TINYINT(1) NOT NULL
        ');
    }
}
