<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/05/16 08:16:10
 */
class Version20200516081608 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            DROP INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool
        ');
        $this->addSql('
            DROP INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP is_visible_in_desktop, 
            DROP ordered_tool_type
        ');
        $this->addSql('
            CREATE UNIQUE INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool (tool_id, user_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool (tool_id, workspace_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool
        ');
        $this->addSql('
            DROP INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD is_visible_in_desktop TINYINT(1) NOT NULL, 
            ADD ordered_tool_type INT NOT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool (
                tool_id, user_id, ordered_tool_type
            )
        ');
        $this->addSql('
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool (
                tool_id, workspace_id, ordered_tool_type
            )
        ');
    }
}
