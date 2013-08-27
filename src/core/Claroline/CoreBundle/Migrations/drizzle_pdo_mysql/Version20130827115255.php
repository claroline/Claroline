<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/27 11:52:56
 */
class Version20130827115255 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX home_tab_unique_name_user ON claro_home_tab
        ");
        $this->addSql("
            DROP INDEX home_tab_unique_name_workspace ON claro_home_tab
        ");
        $this->addSql("
            DROP INDEX home_tab_config_unique_home_tab_user_workspace ON claro_home_tab_config
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user ON claro_home_tab_config (home_tab_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_workspace ON claro_home_tab_config (home_tab_id, workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_user ON claro_home_tab (name, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_unique_name_workspace ON claro_home_tab (name, workspace_id)
        ");
        $this->addSql("
            DROP INDEX home_tab_config_unique_home_tab_user ON claro_home_tab_config
        ");
        $this->addSql("
            DROP INDEX home_tab_config_unique_home_tab_workspace ON claro_home_tab_config
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user_workspace ON claro_home_tab_config (
                home_tab_id, user_id, workspace_id
            )
        ");
    }
}