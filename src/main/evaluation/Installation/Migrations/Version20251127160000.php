<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/29 01:57:50
 */
final class Version20251127160000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM claro_evaluation_certificate WHERE status != "passed" AND status != "completed"
        ');

        $this->addSql('
            DELETE FROM claro_evaluation_sequence_certificate WHERE status != "passed" AND status != "completed"
        ');
    }

    public function down(Schema $schema): void
    {
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
