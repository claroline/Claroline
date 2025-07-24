<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/07/24 11:05:07
 */
final class Version20250724110504 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            ADD is_public TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_resource_node SET is_public = 0
        ');

        $this->addSql('
            UPDATE claro_resource_node AS n
            SET n.is_public = 1
            WHERE EXISTS (
                SELECT rr.id
                FROM claro_resource_rights AS rr
                LEFT JOIN claro_role AS r ON rr.role_id = r.id
                WHERE rr.mask & 1 = 1
                  AND r.name = "ROLE_ANONYMOUS"
                  AND rr.resourceNode_id = n.id
            )
        ');

        $this->addSql('
            UPDATE claro_ordered_tool AS t
            SET t.is_public = 1
            WHERE EXISTS (
                SELECT tr.id
                FROM claro_tool_rights AS tr
                LEFT JOIN claro_role AS r ON tr.role_id = r.id
                WHERE tr.mask & 1 = 1
                  AND r.name = "ROLE_ANONYMOUS"
                  AND tr.ordered_tool_id = t.id
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_node 
            DROP is_public
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
