<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/25 10:39:02
 */
final class Version20250925103859 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX resource_user_evaluation ON claro_resource_user_evaluation
        ');
        $this->addSql('
            DROP INDEX workspace_user_evaluation ON claro_workspace_evaluation
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            CREATE UNIQUE INDEX resource_user_evaluation ON claro_resource_user_evaluation (resource_node, user_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX workspace_user_evaluation ON claro_workspace_evaluation (workspace_id, user_id)
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
