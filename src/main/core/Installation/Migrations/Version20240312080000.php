<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/02/26 06:46:16
 */
final class Version20240312080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            INSERT INTO claro_ordered_tool (uuid, tool_name, context_name, context_id, entity_order, fullscreen)
            SELECT UUID() AS uuid, "transfer" AS tool_name, "workspace" AS context_name, w.uuid AS context_id, 0 AS entity_order, 0 AS fullscreen
            FROM claro_workspace AS w  
            WHERE NOT EXISTS (
                SELECT o2.id 
                FROM claro_ordered_tool AS o2 
                WHERE o2.tool_name = "transfer"
                  AND o2.context_name = "workspace"
                  AND o2.context_id = w.uuid
            )
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
