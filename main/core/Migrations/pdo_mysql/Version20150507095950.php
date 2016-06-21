<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/05/07 09:59:51
 */
class Version20150507095950 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_user_options 
            ADD desktop_mode INT DEFAULT 1 NOT NULL
        ');
        $this->addSql('
            DROP INDEX UNIQ_C10C14EC5E237E06 ON claro_admin_tools
        ');
        $this->addSql('
            CREATE UNIQUE INDEX admin_tool_plugin_unique ON claro_admin_tools (name, plugin_id)
        ');
        $this->addSql('
            DROP INDEX UNIQ_60F909655E237E06 ON claro_tools
        ');
        $this->addSql('
            CREATE UNIQUE INDEX tool_plugin_unique ON claro_tools (name, plugin_id)
        ');
        $this->addSql('
            DROP INDEX UNIQ_76CA6C4F5E237E06 ON claro_widget
        ');
        $this->addSql('
            CREATE UNIQUE INDEX widget_plugin_unique ON claro_widget (name, plugin_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX admin_tool_plugin_unique ON claro_admin_tools
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_C10C14EC5E237E06 ON claro_admin_tools (name)
        ');
        $this->addSql('
            DROP INDEX tool_plugin_unique ON claro_tools
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_60F909655E237E06 ON claro_tools (name)
        ');
        $this->addSql('
            ALTER TABLE claro_user_options 
            DROP desktop_mode
        ');
        $this->addSql('
            DROP INDEX widget_plugin_unique ON claro_widget
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_76CA6C4F5E237E06 ON claro_widget (name)
        ');
    }
}
