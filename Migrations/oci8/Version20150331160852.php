<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/31 04:08:54
 */
class Version20150331160852 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX home_tab_config_unique_home_tab_user
        ");
        $this->addSql("
            DROP INDEX home_tab_config_unique_home_tab_workspace
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user_workspace_type ON claro_home_tab_config (
                home_tab_id, user_id, workspace_id, 
                type
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX home_tab_config_unique_home_tab_user_workspace_type
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user ON claro_home_tab_config (home_tab_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_workspace ON claro_home_tab_config (home_tab_id, workspace_id)
        ");
    }
}