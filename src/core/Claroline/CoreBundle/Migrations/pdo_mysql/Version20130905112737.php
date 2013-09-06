<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/05 11:27:39
 */
class Version20130905112737 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX widget_home_tab_unique_order ON claro_widget_home_tab_config
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX widget_home_tab_unique_order ON claro_widget_home_tab_config (
                widget_id, home_tab_id, widget_order
            )
        ");
    }
}