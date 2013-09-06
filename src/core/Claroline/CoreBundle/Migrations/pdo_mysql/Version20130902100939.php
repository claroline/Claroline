<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/02 10:09:39
 */
class Version20130902100939 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD type VARCHAR(255) NOT NULL, 
            ADD is_visible TINYINT(1) NOT NULL, 
            ADD is_locked TINYINT(1) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP type, 
            DROP is_visible, 
            DROP is_locked
        ");
    }
}