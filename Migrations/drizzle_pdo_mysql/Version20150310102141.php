<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/10 10:21:44
 */
class Version20150310102141 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool (
                tool_id, user_id, ordered_tool_type
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool (
                tool_id, workspace_id, ordered_tool_type
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool
        ");
    }
}