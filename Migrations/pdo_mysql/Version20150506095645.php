<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/05/06 09:56:47
 */
class Version20150506095645 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_C10C14EC5E237E06 ON claro_admin_tools
        ");
        $this->addSql("
            CREATE UNIQUE INDEX admin_tool_plugin_unique ON claro_admin_tools (name, plugin_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_60F909655E237E06 ON claro_tools
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tool_plugin_unique ON claro_tools (name, plugin_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_76CA6C4F5E237E06 ON claro_widget
        ");
        $this->addSql("
            CREATE UNIQUE INDEX widget_plugin_unique ON claro_widget (name, plugin_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX admin_tool_plugin_unique ON claro_admin_tools
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C10C14EC5E237E06 ON claro_admin_tools (name)
        ");
        $this->addSql("
            DROP INDEX tool_plugin_unique ON claro_tools
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_60F909655E237E06 ON claro_tools (name)
        ");
        $this->addSql("
            DROP INDEX widget_plugin_unique ON claro_widget
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_76CA6C4F5E237E06 ON claro_widget (name)
        ");
    }
}